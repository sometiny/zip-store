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
    public function __construct($contents, $name)
    {

        $this->setFileName($name);

        $fileSize = strlen($contents);

        $this->setCompressedSize($fileSize);
        $this->setUncompressedSize($fileSize);

        $this->setLastModify(time());

        $this->contents = $contents;
    }

    public function writeTo($output)
    {
        $this->setCrc32(crc32($this->contents));
        $this->writeFileHeader($output);

        $totalSize = $this->getUncompressedSize();
        while ($totalSize > 0) {
            $sent = fwrite($output, $this->contents, 0x10000);

            $totalSize -= $sent;
        }
    }
}
