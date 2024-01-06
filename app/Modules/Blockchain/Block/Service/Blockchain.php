<?php
namespace App\Modules\Blockchain\Block\Service;

use App\Modules\Blockchain\Block\Domain\Block;
use Carbon\Carbon;

class Blockchain
{
    const HEADERS = array(
        'Version',
        'CreationDate',
        'PreviousHash',
        'BlockHash',
        'DataLength',
        'Data',
    );

    protected $name = 'RadgeBlockchain';

    protected $version = '0_0_1';

    protected $blocksDir = __DIR__ . '/files/blockchain';

    protected $blocksFile;

    /**
     * Sets up all the options to the blockchain and instance the chain
     */
    public function __construct()
    {
        $this->initializeFiles();
    }

    /**
     * Initialize the files needed
     *
     * @return void
     */
    protected function initializeFiles()
    {
        $this->blocksFile = $this->blocksDir . '/' . "{$this->name}.{$this->version}.csv";
        if (! file_exists($this->blocksFile)) {
            file_put_contents($this->blocksFile, implode(';', self::HEADERS) . "\n");
        }

        if (! is_writable($this->blocksFile)) {
            throw new \UnexpectedValueException("Can't create blocks file at '{$this->blocksFile}'", 1);
        }
    }

    /**
     * Add a new block to the chain
     *
     * @param Block $block
     * @param bool $isGenesisBlock
     *
     * @return Block the new block added
     */
    public function addBlock(Block $block, $isGenesisBlock = false)
    {
        if (! $isGenesisBlock) {
            $previousBlock = $this->getLastBlock();
            if ($previousBlock instanceOf Block) {
                $block->setPreviousHash($previousBlock->getBlockHash());
            }
        }

        $block->generateBlockHash();

        $blockInBlockchain =
            $this->version . ';' .
            $block->getCreationDate()->format(Block::FORMAT_DATES) . ';' .
            $block->getPreviousHash() . ';' .
            $block->getBlockHash() . ';' .
            $block->getDataLength() . ';' .
            '"' . addslashes($block->getData()) . '"';

        file_put_contents($this->blocksFile, file_get_contents($this->blocksFile) . $blockInBlockchain . "\n");

        return $block;
    }

    /**
     * Get the last block added to the chain
     *
     * @return Block
     */
    public function getLastBlock()
    {
        $fp = fopen($this->blocksFile, 'r');

        $pos = -2;
        $lastBlockLine = '';
        $character = '';
        do {
            $lastBlockLine = $character . $lastBlockLine;
            fseek($fp, $pos--, SEEK_END);
            $character = fgetc($fp);
        } while ($character != "\n");

        fclose($fp);

        $lastBlockColumns = explode(';', $lastBlockLine);
        $creationDate = new Carbon($lastBlockColumns[1]);
        $previousHash = $lastBlockColumns[2];
        $blockHash = $lastBlockColumns[3];
        $data = stripslashes($lastBlockColumns[5]);

        $lastBlock = new Block($data, $creationDate);
        $lastBlock->setPreviousHash($previousHash);
        $lastBlock->setBlockHash($blockHash);

        return $lastBlock;
    }

    /**
     * Generate the genesis Block
     *
     * @return Block
     */
    public function generateGenesisBlock()
    {
        $genesis = new Block('Genesis Block');

        return $this->addBlock($genesis, true);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getBlocksDir()
    {
        return $this->blocksDir;
    }

    public function getBlocksFile()
    {
        return $this->blocksFile;
    }
}
