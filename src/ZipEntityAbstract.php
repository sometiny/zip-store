<?php
namespace Jazor\Zip\Store;

abstract class ZipEntityAbstract
{
    private $compressVersion = 0x031e;
    private $decompressVersion = 0xa;
    private $flag = 0;
    private $compressionMethod = 0;
    private $lastModifyTime = 0;
    private $lastModifyDate = 0;
    private $crc32 = 0;
    private $compressedSize = 0;
    private $uncompressedSize = 0;
    private $fileNameLength = 0;
    private $extraFieldLength = 0;
    private $fileCommentLength = 0;
    private $diskNumberStart = 0;
    private $internalFileAttributes = 0;
    private $externalFileAttributes = 0;
    private $offset = 0;
    private $fileName = '';
    private $extraField = '';
    private $fileComment = '';
    /**
     * @var null
     */
    private $file;

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @throws \Exception
     */
    public function setFileName(string $fileName)
    {

        /**
         * replace '\' with '/' and trim start '/'
         */
        $fileName = str_replace('\\', '/', $fileName);
        $fileName = ltrim($fileName, '/');

        $this->fileName = $fileName;
        $this->fileNameLength = strlen($fileName);

        if (!mb_check_encoding($fileName, 'ascii')) {
            if (!mb_check_encoding($fileName, 'utf-8')) throw new \Exception('file name must be utf-8 encoding');
            $this->setUtf8EncodingFlag();
        }
    }

    /**
     * @return int
     */
    public function getFileNameLength(): int
    {
        return $this->fileNameLength;
    }

    /**
     * @param int $fileNameLength
     */
    public function setFileNameLength(int $fileNameLength)
    {
        $this->fileNameLength = $fileNameLength;
    }

    /**
     * @return int
     */
    public function getUncompressedSize(): int
    {
        return $this->uncompressedSize;
    }

    /**
     * @param int $uncompressedSize
     */
    public function setUncompressedSize(int $uncompressedSize)
    {
        $this->uncompressedSize = $uncompressedSize;
    }

    /**
     * @return int
     */
    public function getCompressedSize(): int
    {
        return $this->compressedSize;
    }

    /**
     * @param int $compressedSize
     */
    public function setCompressedSize(int $compressedSize)
    {
        $this->compressedSize = $compressedSize;
    }

    /**
     * @return int
     */
    public function getCrc32(): int
    {
        return $this->crc32;
    }

    /**
     * @param int $crc32
     */
    public function setCrc32(int $crc32)
    {
        $this->crc32 = $crc32;
    }

    /**
     * 0x5455, 0x7875
     * @param int $id
     * @param $data
     */
    private function addExtra(int $id, $data)
    {
        $this->extraField .= pack('vv', $id, strlen($data)) . $data;
        $this->extraFieldLength = strlen($this->extraField);
    }

    public function getLastModifyDateString(): string
    {
        $dt = $this->lastModifyDate;
        return sprintf('%u-%02u-%02u', 1980 + (($dt >> 9) & 0X7F), ($dt >> 5) & 0XF, $dt & 0X1F);
    }

    public function getLastModifyTimeString(): string
    {
        $dt = $this->lastModifyTime;
        return sprintf('%02u:%02u:%02u', ($dt >> 11) & 0X1F, ($dt >> 5) & 0X3F, ($dt & 0X1F) * 2);
    }

    public function setUtf8EncodingFlag($utf8 = true)
    {
        if ($utf8) {
            $this->flag |= 0x0800;
            return;
        }
        $this->flag &= 0xf7ff;
    }

    public function setCRC32Flag($endOfData = true)
    {
        if ($endOfData) {
            $this->flag |= 0x0008;
            return;
        }
        $this->flag &= 0xfff7;
    }

    /**
     * @param int $time
     */
    public function setLastModifyTime(int $time)
    {
        $hour = intval(date('G', $time));
        $minute = intval(ltrim(date('i', $time), '0'));
        $second = floor(intval(ltrim(date('s', $time), '0')) / 2);

        $this->lastModifyTime = (($hour & 0X1F) << 11) | (($minute & 0x3f) << 5) | ($second & 0x1f);
    }

    /**
     * @param int $time
     */
    public function setLastModifyDate(int $time)
    {
        $year = intval(date('Y', $time)) - 1980;
        $month = intval(date('n', $time));
        $day = intval(date('j', $time));

        $this->lastModifyDate = (($year & 0X7F) << 9) | (($month & 0xf) << 5) | ($day & 0x1f);
    }

    public function setLastModify(int $time)
    {
        $this->setLastModifyDate($time);
        $this->setLastModifyTime($time);
    }

    public function getLastModify(): string
    {
        return $this->getLastModifyDateString() . ' ' . $this->getLastModifyTimeString();
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;
    }

    /**
     * get local entity header
     * @return string
     */
    public function getLocalEntityHeader(): string
    {
        return pack('NvvvvvVVVvv', 0x504b0304,
                $this->decompressVersion,
                $this->flag,
                $this->compressionMethod,
                $this->lastModifyTime,
                $this->lastModifyDate,
                $this->crc32,
                $this->compressedSize,
                $this->uncompressedSize,
                $this->fileNameLength,
                $this->extraFieldLength) . $this->fileName . $this->extraField;
    }

    /**
     * get local entity header size
     * @return int
     */
    public function getLocalEntitySize(): int
    {
        return 30 + strlen($this->fileName) + $this->compressedSize + $this->extraFieldLength;
    }

    /**
     * get central directory header
     * @return string
     */
    public function getCentralDirectoryHeader(): string
    {
        return pack('NvvvvvvVVVvvvvvVV', 0x504b0102,
                $this->compressVersion,
                $this->decompressVersion,
                $this->flag,
                $this->compressionMethod,
                $this->lastModifyTime,
                $this->lastModifyDate,
                $this->crc32,
                $this->compressedSize,
                $this->uncompressedSize,
                $this->fileNameLength,
                $this->extraFieldLength,
                $this->fileCommentLength,
                $this->diskNumberStart,
                $this->internalFileAttributes,
                $this->externalFileAttributes,
                $this->offset) . $this->fileName . $this->extraField . $this->fileComment;
    }

    /**
     * get central directory header size
     * @return int central directory header size
     */
    public function getCentralDirectoryHeaderSize(): int
    {
        return 46 + strlen($this->fileName) + $this->extraFieldLength + $this->fileCommentLength;
    }

    /**
     * write local entity header
     * @param $output
     * @return int local entity size, include data
     */
    public function writeLocalEntityHeader($output): int
    {
        fwrite($output, $this->getLocalEntityHeader());
        return $this->getLocalEntitySize();
    }

    /**
     * write central directory header
     * @param $output
     * @return int central directory header size
     */
    public function writeCentralDirectoryHeader($output): int
    {
        fwrite($output, $this->getCentralDirectoryHeader());
        return $this->getCentralDirectoryHeaderSize();
    }

    /**
     * write data to output
     *
     * output can be file resource or php://output, and so on
     * @param $output
     * @return mixed
     */
    public abstract function writeTo($output): int;
}
