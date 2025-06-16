<?php


namespace App\Modules\User\Infrastructure\Controller;

use App\Enums\EmployeePosition;
use App\Http\Controllers\Controller;
use App\Mail\DeleteAccount;
use App\Modules\Base\Infrastructure\Service\AccountManager;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Wallet\Domain\Wallet;
use App\Modules\EmployeeManager\Domain\Employee;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\User\Domain\Identification;
use App\Modules\User\Domain\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Agustind\Ethsignature;
use PragmaRX\Google2FA\Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

class Api extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws
     */
    public function login(Request $request)
    {
        $account = $request->route('account');
        if (empty($account)) {
            return response()->json('Account not found', 500);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        /** @var User $user */
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if 2FA is enabled for this user
        if ($user->two_factor_enabled) {
            $return = new \stdClass();
            $return->two_factor_required = true;
            $return->email = $user->email;

            return response()->json($return);
        }

        $return = new \stdClass();
        $return->token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json($return);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        /** @var User $user */
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $lastToken = $user->createToken("website")->plainTextToken;
        $userToReportDeleteAccount = User::find(2);

        Mail::to($userToReportDeleteAccount)->send(new DeleteAccount($user, $lastToken));

        return response()->json("Cuenta en proceso de eliminación");
    }

    /**
     * @return JsonResponse
     * @throws
     */
    public function logout(Request $request)
    {
        $account = $request->route('account');
        if (empty($account)) {
            return response()->json('Account not found', 500);
        }

        auth()->user()->tokens()->delete();

        return response()->json('Exit to logout');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $account = $request->route('account');
        if (empty($account)) {
            return response()->json('Account not found', 500);
        }

        $checkExistIdentification = Identification::where('account', strtolower($account))->first();

        $accountCreated = false;
        if (!$checkExistIdentification) {
            try {
                $accountCreated = AccountManager::createAccount($account);
            } catch (\Exception $e) {
                return response()->json('Error to create account ' . $e->getMessage(), 500);
            }
            if (!$accountCreated) {
                return response()->json('Error to create account', 500);
            }
        }
        $accountConnected = AccountManager::connectToAccount($request);
        if ($accountCreated && $accountConnected) {
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--force'    => true, // Obligatorio para evitar confirmaciones en producción
            ]);
        }

        $data = $request->validate(User::VALIDATION_COTNEXT);

        event(new Registered($user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'newsletter' => $data['newsletter'],
        ])));

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->balance = 0;
        $wallet->save();

        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->description = 'Aprendiz';
        $profile->details = '';
        $profile->avatar = '';
        $profile->plumas = 2;
        $profile->oro = 0;
        $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = 2;
        $newBlockchainHistorical->memo = "Register";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        if ($accountCreated) {
            $employee = new Employee();
            $employee->user_id = $user->id;
            $employee->name = $data['name'];
            $employee->email = $data['email'];
            $employee->position = EmployeePosition::fromName('MANAGER');
            $employee->phone_number = '';
            $employee->save();
        }

        return response()->json('User registered');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotSendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json('Reset link sent')
            : response()->json('Error to send reset link', 500);
    }

    /**
     * Set the user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $user->password = Hash::make($password);
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Reset the given user's password.
     *
     * @param  User $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password, $login = true)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        if ($login) {
            $this->guard()->login($user);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotReset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? response()->json('Password reset')
            : response()->json('Error to reset password', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPasswordLogged(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'currentPassword' => 'required',
            'password' => 'required|min:8',
        ]);

        // Verificar si la contraseña actual es correcta
        if (!Hash::check($data['currentPassword'], $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => ['Current password is incorrect.'],
            ]);
        }


        $this->resetPassword($user, $data['password'], false);

        return response()->json('Password reset');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws
     */
    public function verify(Request $request)
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');

        if (! hash_equals((string) $request->route('id'), (string) $request->user()->getKey())) {
            throw new AuthorizationException;
        }

        if (! hash_equals((string) $request->route('hash'), sha1($request->user()->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('User already has an verified email');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json('Verified');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(\App\Modules\User\Transformers\User::collection(User::orderBy('name', 'desc')->get()));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function linkWallet(Request $request)
    {
        $data = $request->validate(['address' => 'required|string', 'signature' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $signature = new Ethsignature();

        $is_valid = $signature->verify('Bienvenid@ a Mad Fénix!', $data['signature'], $data['address']);

        $addressSaved = false;
        if ($is_valid) {
            $user->eth_wallet = $data['address'];
            $addressSaved = $user->save();
        }

        return $addressSaved
            ? response()->json('Wallet vinculada')
            : response()->json('Error al verificar el mensaje', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setIP(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $user->ip = $request->ip();
        $ipSaved = $user->save();

        return $ipSaved
            ? response()->json($request->ip())
            : response()->json('Error al vincular la IP: ' . $request->ip(), 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getIP(Request $request)
    {
        $data = $request->validate(['userAndId' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $userAndId = $data['userAndId'];
        $userAndIdArray = explode("#", $userAndId);
        if (count($userAndIdArray) != 2) {
            return response()->json('Malformed parameters', 500);
        }

        $user = $userAndIdArray[0];
        $id = $userAndIdArray[1];

        $users = DB::table('users')
            ->where('id', '=', $id)
            ->whereRaw('LOWER(`name`) LIKE ? ',['%'.trim(strtolower($user)).'%'])
            ->select(['ip'])
            ->limit(1)->get();
        if (count($users) <= 0) {
            return response()->json('User not found', 500);
        }


        return response()->json($users->get(0)->ip);
    }

    /**
     * Generate 2FA secret key and QR code
     *
     * @return JsonResponse
     */
    public function generate2fa()
    {
        /** @var User $user */
        $user = auth()->user();

        // Initialize the 2FA class
        $google2fa = new Google2FA();

        // Generate a new secret key
        $secretKey = $google2fa->generateSecretKey();

        // Save the secret key to the user
        $user->two_factor_secret = $secretKey;
        $user->two_factor_pre_enabled = true;
        $user->save();

        // Generate the QR code
        $google2faQR = new Google2FAQRCode();
        $qrCodeUrl = $google2faQR->getQRCodeInline(
            'weLore',
            $user->email,
            $secretKey
        );

        return response()->json([
            'secret' => $secretKey,
            'qr_code' => $qrCodeUrl
        ]);
    }

    /**
     * Confirm 2FA setup by validating a code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirm2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        /** @var User $user */
        $user = auth()->user();

        // Check if 2FA is pre-enabled
        if (!$user->two_factor_pre_enabled) {
            return response()->json('Two-factor authentication not initialized', 400);
        }

        // Initialize the 2FA class
        $google2fa = new Google2FA();

        // Verify the code
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            // Enable 2FA
            $user->two_factor_enabled = true;
            $user->save();

            return response()->json('Two-factor authentication enabled');
        }

        return response()->json('Invalid verification code', 400);
    }

    /**
     * Verify 2FA code during login
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'device_name' => 'required'
        ]);

        /** @var User $user */
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json('User not found', 404);
        }

        // Check if 2FA is enabled
        if (!$user->two_factor_enabled) {
            return response()->json('Two-factor authentication not enabled for this user', 400);
        }

        // Initialize the 2FA class
        $google2fa = new Google2FA();

        // Verify the code
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if ($valid) {
            $return = new \stdClass();
            $return->token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json($return);
        }

        return response()->json('Invalid verification code', 400);
    }
}
