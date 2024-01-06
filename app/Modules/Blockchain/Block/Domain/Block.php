<?php
namespace App\Modules\Blockchain\Block\Domain;

use Carbon\Carbon;

class Block
{
    const PREVIOUS_GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    const BLOCK_DATA_LIMIT = 50000;

    const HASH_ALGORITHM = 'sha256';

    const FORMAT_DATES = 'Y-m-d\TH:i:s';

    /**
     * @var Carbon
     */
    protected $creationDate;

    /**
     * @var string
     */
    protected $previousHash = self::PREVIOUS_GENESIS_HASH;

    /**
     * @var string
     */
    protected $blockHash;

    /**
     * @var int
     */
    protected $dataLength;

    /**
     * @var string
     */
    protected $data;

    /**
     * Sets up the data to be saved on block and instance the object
     *
     * @param mixed $data can be string/array/object
     * @param Carbon $creationDate
     */
    public function __construct($data, Carbon $creationDate = null)
    {
        $this->data             = json_encode($data);
        $this->dataLength       = strlen($this->data);
        $this->creationDate     = ($creationDate) ?? new Carbon();

        if ($this->dataLength > self::BLOCK_DATA_LIMIT) {
            throw new \InvalidArgumentException(
                "'Data' field can't have more than " . self::BLOCK_DATA_LIMIT . " characters"
            );
        }
    }

    /**
     * Generate the hash to the actual block
     *
     * @return bool
     */
    public function generateBlockHash()
    {
        $dataToHash =
            $this->getCreationDate()->format(self::FORMAT_DATES) .
            $this->getPreviousHash() .
            $this->getDataLength() .
            $this->getData();
        $this->setBlockHash(hash(self::HASH_ALGORITHM, $dataToHash));

        return true;
    }

    /**
     * @return Carbon
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return string
     */
    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    /**
     * @return string
     */
    public function getBlockHash()
    {
        return $this->blockHash;
    }

    /**
     * @return int
     */
    public function getDataLength()
    {
        return $this->dataLength;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $previousHash
     */
    public function setPreviousHash(string $previousHash): void
    {
        $this->previousHash = $previousHash;
    }

    /**
     * @param string $blockHash
     */
    public function setBlockHash(string $blockHash): void
    {
        $this->blockHash = $blockHash;
    }
}
