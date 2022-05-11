<?php
namespace Jazor\Zip\Store;

class ZipStore
{

    private $entities = [];

    /**
     * ZipStore constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param string $file
     * @param string|null $name
     * @return ZipStore
     * @throws \Exception
     */
    public function addFile(string $file, string $name = null): ZipStore
    {
        $this->entities[] = new ZipEntity($file, $name);
        return $this;
    }

    /**
     * @param string $dir
     * @param string|null $base
     * @return ZipStore
     * @throws \Exception
     */
    public function addDirectory(string $dir, $base = ''): ZipStore
    {
        $dir = rtrim($dir, '/\\');
        $handle = opendir($dir);
        if (!$handle) throw new \Exception('can not open directory');
        try {
            while(($name = readdir($handle)) !== false) {
                if ($name === '.' || $name === '..') continue;
                $fullPath = $dir . DIRECTORY_SEPARATOR . $name;
                if(is_file($fullPath)){
                    $this->addFile($fullPath, $base . $name);
                    continue;
                }
                $this->addDirectory($fullPath, $base . $name . '/');
            }
            return $this;
        } finally {
            closedir($handle);
        }
    }

    /**
     * send zip contents to client
     * @param $downloadFileName
     * @throws \Exception
     */
    public function send($downloadFileName)
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . $downloadFileName . '"');
        $this->save('php://output');
    }

    /**
     * save zip contents to dest
     * @param $dest
     * @throws \Exception
     */
    public function save($dest)
    {
        /**
         * @var ZipEntity $entity
         */
        $output = fopen($dest, 'wb');
        if (!$output) throw new \Exception('can not open zip archive');
        try {
            $entities = $this->entities;
            $offset = 0;
            $size = 0;
            foreach ($entities as $entity) {
                $entity->setOffset($offset);
                $entity->writeTo($output);
                $offset += $entity->getLocalEntitySize();
            }
            foreach ($entities as $entity) {
                fwrite($output, $entity->getCentralFileHeader());
                $size += $entity->getCentralEntitySize();
            }

            fwrite($output, pack('NvvvvVVv', 0x504b0506,
                0, 0,
                count($entities), count($entities),
                $size, $offset, 0));
            fflush($output);
        } finally {
            fclose($output);
        }
    }
}
