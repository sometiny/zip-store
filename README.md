### zip store for php
only create store, with no compression.

```php
$zip = new ZipStore();

//simple file
$zip->addFile("{$base}store.txt");

//file with extra name
$zip->addFile("{$base}extra.txt", 'files/extra.txt');
$zip->addFile("{$base}你好.txt", 'files/你好.txt');

//directory with base name
$zip->addDirectory("{$base}files", 'extra-files');

//raw contents
$zip->addContents('i am hello world!', 'my name.jpg');

//save to file
$zip->save("{$base}dest.zip");

//or send to client
//$zip->send('dest.zip');
```
