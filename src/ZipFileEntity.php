<?php
namespace Jazor\Zip\Store;

class ZipFileEntity extends ZipEntityAbstract
{
    private $file = '';

    /**
     * ZipEntity constructor.
     * @param null $file
     * @param null $name
     * @throws \Exception
     */
    public function __construct($file = null, $name = null)
    {
        if (!is_file($file)) throw new \Exception(sprintf('file \'%s\' not found.', $file));

        if (empty($name)) {
            $pos = strrpos($file, DIRECTORY_SEPARATOR);
            if ($pos === false) throw new \Exception(sprintf('file path \'%s\' error.', $file));
            $name = substr($file, $pos + 1);
        }


        $this->setFileName($name);


        $fileModifyTime = filemtime($file);
        $fileSize = filesize($file);

        $this->setCompressedSize($fileSize);
        $this->setUncompressedSize($fileSize);

        $this->setLastModify($fileModifyTime);

        $this->file = $file;
    }

    public function writeTo($output)
    {
        $this->setCrc32(unpack('N', hash_file('crc32b', $this->file, true))[1]);
        $this->writeFileHeader($output);

        $input = fopen($this->file, 'rb');
        if (!$input) throw new \Exception('can not open file for read: ' . $this->file);
        try {
            while (!feof($input)) {
                fwrite($output, fread($input, 0x10000));
            }
        } finally {
            fclose($input);
        }
    }
}
