<?php
namespace Jazor\Zip\Store;

class ZipContentsEntity extends ZipEntityAbstract
{
    private $contents = '';

    /**
     * ZipContentsEntity constructor.
     * @param string $contents
     * @param string $name
     * @throws \Exception
     */
    public function __construct(string $contents, string $name)
    {

        $this->setFileName($name);

        $fileSize = strlen($contents);

        $this->setCompressedSize($fileSize);
        $this->setUncompressedSize($fileSize);

        $this->setLastModify(time());

        $this->contents = $contents;
    }

    /**
     * @param $output
     * @return int
     */
    public function writeTo($output): int
    {
        $this->setCrc32(crc32($this->contents));
        $size = $this->writeLocalEntityHeader($output);

        $totalSize = $this->getUncompressedSize();
        while ($totalSize > 0) {
            $sent = fwrite($output, $this->contents, 0x10000);

            $totalSize -= $sent;
        }
        return $size;
    }
}
