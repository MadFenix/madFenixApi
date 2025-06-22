<?php

namespace App\Modules\Base\Infrastructure\Service;

use App\Modules\User\Domain\Identification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccountManager
{
    static public function getFilenameAccount($account): string
    {
        return 'account_' . strtolower($account) . '.json';
    }

    static protected function getConnectionDataByAccount($account): \stdClass|bool
    {
        $path = self::getFilenameAccount($account);

        $data = false;

        if (Storage::exists($path)) {
            $json = Storage::get($path);
            $data = json_decode($json);
        }

        return $data;
    }

    static public function getAccountFromHost(Request $request)
    {
        $return = new \stdClass();
        $return->url = $request->header('Referer');
        $parsedUrl = parse_url($return->url);
        $return->scheme = $parsedUrl['scheme'];
        $return->host = explode(':', $parsedUrl['host'])[0];
        $return->path = '';
        $return->account = '';
        if ($return->host == 'https://our.welore.io' || $return->host == 'http://localhost') {
            $return->path = $request->header('X-Current-Path');
            $segments = explode('/', trim($return->path, '/'));
            if (empty($segments[0])) {
                throw new \Exception('Invalid account');
            }
            $return->account = $segments[0];
        } else {
            $return->account = explode('.', $return->host)[0];
        }

        return $return;
    }

    static public function connectToAccount(Request $request, $account = ''): bool
    {
        if (empty($account)) {
            $account = $request->route('account');
        }
        $account = Utilities::clearName($account);
        if ($account == 'host') {
            $accountFromHost = self::getAccountFromHost($request);
            $account = $accountFromHost->account;
        }

        $data = self::getConnectionDataByAccount($account);

        if ($data) {
            Config::set('database.connections.tenant', [
                'driver'    => 'mysql',
                'host'      => $data->bbdd_host,
                'database'  => $data->bbdd_name,
                'username'  => $data->bbdd_username,
                'password'  => $data->bbdd_password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]);
            Config::set('database.default', 'tenant');

            DB::purge('tenant');
            DB::reconnect('tenant');

            return true;
        }

        return false;
    }

    static public function createNewBBDD($username, $password, $dbHost, $dbName, $dbUser, $dbPassword, $command, $urlApi): void
    {
        $args = array('responseType' => 'Json',
            'hosting' => $dbHost,
            'ddbbName' => $dbName,
            'userName' => $dbUser,
            'userPassword' => $dbPassword,
            'accessHost' => 'any',
            'command' => $command,
        ) ;
        $args = ( is_array ( $args ) ? http_build_query ( $args, '', '&' ) : $args );
        $headers = array();
        $handle = curl_init($urlApi);
        if( $handle === false ) // error starting curl
        {
            throw new \Exception('0 - Couldnot start curl');
        } else {
            curl_setopt ( $handle, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt ( $handle, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $handle, CURLOPT_URL, $urlApi );

            curl_setopt( $handle, CURLOPT_USERPWD, $username.':'.$password );
            curl_setopt( $handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );

            curl_setopt( $handle, CURLOPT_TIMEOUT, 60 );
            curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 4); // set higher if you get a "28 - SSL connection timeout" error

            curl_setopt ( $handle, CURLOPT_HEADER, true );
            curl_setopt ( $handle, CURLOPT_HTTPHEADER, $headers );

            $curlversion = curl_version();
            curl_setopt ( $handle, CURLOPT_USERAGENT, 'PHP '.phpversion().' + Curl '.$curlversion['version'] );
            curl_setopt ( $handle, CURLOPT_REFERER, null );

            curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false ); // set false if you get a "60 - SSL certificate problem" error

            curl_setopt ( $handle, CURLOPT_POSTFIELDS, $args );
            curl_setopt ( $handle, CURLOPT_POST, true );

            $response = curl_exec ( $handle );

            if ($response)
            {
                $response = substr( $response,  strpos( $response, "\r\n\r\n" ) + 4 ); // remove http headers
                // parse response

                $responseDecoded = json_decode($response, true);
                if( $responseDecoded === false )
                {
                    throw new \Exception('0 - Invalid json respond');
                }
                else
                {
                    // parse response
                    if( false == isset($responseDecoded['responseCode']) )
                    {
                        throw new \Exception('0 - Unexpected internal error');
                    }
                    else if( $responseDecoded['responseCode'] == 1000  )
                    {
                        $data = $responseDecoded['data'];
                    }
                    else
                    {
                        // normal error
                        $errors = $responseDecoded['errors'];

                        throw new \Exception('0 - Error logged');
                    }
                }
            }
            else // http response code != 200
            {
                $error = curl_errno ( $handle ) . ' - ' . curl_error ( $handle );

                throw new \Exception($error);
            }

            curl_close($handle);
        }
    }

    static public function createAccount($account): bool
    {
        $lastIdentification = Identification::orderBy('id', 'desc')->first();
        $currentIndex = 1;
        if ($lastIdentification) {
            $currentIndex = $lastIdentification->id + 1;
        }

        // Create database to user
        $urlApi = 'https://dinahosting.com/special/api.php';
        $username = env('DINAHOSTING_USER');
        $password = env('DINAHOSTING_PASS');
        $command = 'Hosting_Ddbb_Mysql_Create';
        $dbHost = 'madfenix.com';
        $dbName = 'we_af_' . $currentIndex . 'com';
        $dbUser = $dbName;
        $dbPassword = Utilities::generateRandomPassword();
        if (!$urlApi || !$username || !$password || !$dbHost || !$command || !$dbUser || !$dbName || !$dbPassword) {
            throw new \Exception('Cannot get hosting data from config');
        }

        $newIdentifiaction = new Identification();
        $newIdentifiaction->account = strtolower($account);
        $newIdentifiaction->host = 'https://our.welore.io/' . strtolower($account);
        $newIdentifiaction->bbdd_host = $dbHost;
        $newIdentifiaction->bbdd_name = $dbName;
        $newIdentifiaction->bbdd_username = $dbUser;
        $newIdentifiaction->bbdd_password = $dbPassword;
        $identificationSaved = $newIdentifiaction->save();
        if (!$identificationSaved) {
            throw new \Exception('Cannot create new identification');
        }

        try {
            self::createNewBBDD($username, $password, $dbHost, $dbName, $dbUser, $dbPassword, $command, $urlApi);
        } catch (\Exception $e) {
            $newIdentifiaction->delete();
            throw new \Exception('Cannot create new bbdd ' . $e->getMessage());
        }

        $json = json_encode($newIdentifiaction, JSON_PRETTY_PRINT);

        Storage::put(self::getFilenameAccount($account), $json);

        return true;
    }
}
