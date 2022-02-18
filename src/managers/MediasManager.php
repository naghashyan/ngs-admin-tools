<?php


namespace ngs\AdminTools\managers;


use ngs\AdminTools\dal\dto\MediasDto;
use ngs\AdminTools\dal\mappers\MediasMapper;
use ngs\AdminTools\managers\AbstractCmsManager;

class MediasManager extends AbstractCmsManager
{

    const IMAGE_HANDLERS = [
        IMAGETYPE_JPEG => [
            'load' => 'imagecreatefromjpeg',
            'save' => 'imagejpeg',
            'quality' => 100
        ],
        IMAGETYPE_PNG => [
            'load' => 'imagecreatefrompng',
            'save' => 'imagepng',
            'quality' => 0
        ],
        IMAGETYPE_GIF => [
            'load' => 'imagecreatefromgif',
            'save' => 'imagegif'
        ]
    ];

    private $thumbOptions = [
        'small' => [150, 150],
        'medium' => [500, 500],
        'big' => [800, 800]
    ];


    private $imageTypes = ['png', 'jpeg', 'jpg'];

    private static ?MediasManager $instance = null;

    public static function getInstance(): MediasManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function getMapper(): MediasMapper
    {
        return MediasMapper::getInstance();
    }


    public function createDto()
    {
        return $this->getMapper()->createDto();
    }

    /**
     * create media, if image will create also thumbnails
     *
     * @param string $filePath
     * @param string $fileName
     * @param int $itemId
     * @param string $itemType
     * @param string $description
     * @param null|number $mainImageIndex
     * @return bool
     * @throws \ngs\exceptions\DebugException
     */
    public function createMediaFromFile(string $filePath, string $fileName, int $itemId, string $itemType, string $description = "", $mainImageIndex = null) {
        $mediaId = $this->insertFileToDb($fileName, $itemId, $itemType, $description);

        if($mainImageIndex !== null && $mediaId) {
            $this->updateMainImageOfCurrentItem($itemId, $itemType, $mediaId);
        }

        if(!$mediaId) {
            return false;
        }
        $fileNameInfo = pathinfo($fileName);

        $extension = strtolower($fileNameInfo['extension']);
        $type = $this->getTypeByExtension($extension);
        $subFolder = $type === "image" ? "images" : "files";
        $folderToSave = $this->defineFolder($mediaId, $subFolder);
        $newPath = $folderToSave . '/' . $mediaId . '.' . $extension;

        if(strpos($filePath, 'http') !== false) {
            $imageToSet = file_get_contents($filePath);
            $uploaded = file_put_contents($newPath, $imageToSet);
        }
        else {
            $uploaded = move_uploaded_file($filePath, $newPath);
        }

        if($uploaded === false) {
            $this->removeMediaById($mediaId);
            return false;
        }

        if($type === "image") {
            $this->createImageThumbnails($newPath, $mediaId, $extension);
        }

        return true;
    }


    /**
     * @param string $extension
     * @return bool
     */
    public function getTypeByExtension(string $extension) {
        return in_array(strtolower($extension), $this->imageTypes) ? 'image' : 'file';
    }


    /**
     * return media file info by type and id
     *
     * @param $id
     * @param $mediaType
     * @param null $thumb
     * @return array|null
     */
    public function getMedia($id, $mediaType, $thumb = null) {
        $media = $this->getItemById($id);
        if(!$media) {
            return null;
        }
        if($thumb && !$this->thumbOptions[$thumb]) {
            return null;
        }

        $folder = $this->defineFolder($id, $mediaType, $thumb);


        $extension = $media->getExtension();
        $extension = strtolower($extension);

        return [
            'path' => $folder . '/' . $id . '.' . $extension, 'media' => $media,
            'original_name' => $media->getOriginalName(),
            'extension' => $extension
        ];
    }


    /**
     * return item main image
     *
     * @param $objectId
     * @param $objectType
     * @return mixed
     */
    public function getMainImage($objectId, $objectType) {
        $mapper = $this->getMapper();
        $item = $mapper->getMainImage($objectId, $objectType);
        return $item;
    }


    /**
     * returns true if media for this object with given name exists
     * @param int $objectKey
     * @param string $objectType
     * @param string $mediaName
     * @return bool
     */
    public function itemHasMedia(int $objectKey, string $objectType, string $mediaName) {
        $mapper = $this->getMapper();
        $media = $mapper->getItemMediaByName($objectKey, $objectType, $mediaName);
        return !!$media;
    }


    /**
     * create media record in DB
     *
     * @param string $fileName
     * @param int $objectKey
     * @param string $objectType
     * @param string $description
     *
     * @return int
     */
    public function insertFileToDb(string $fileName, int $objectKey, string $objectType, string $description = ""): int
    {
        $fileNameInfo = pathinfo($fileName);
        $extension = strtolower($fileNameInfo['extension']);
        $type = $this->getTypeByExtension($extension);

        $params = [
            'type' => $type,
            'extension' => strtolower($extension),
            'object_type' => $objectType,
            'object_key' => $objectKey,
            'original_name' => $fileName,
            'description' => $description
        ];

        $dto = $this->createItem($params);
        return $dto->getId();
    }


    /**
     * clone old media to new one
     *
     * @param int $mediaId
     * @param int $newItemId
     * @return bool
     *
     * @throws \ngs\exceptions\DebugException
     */
    public function cloneImage(int $mediaId, int $newItemId) {
        /** @var MediasDto $mediaDto */
        $mediaDto = $this->getItemById($mediaId);
        if(!$mediaDto) {
            return false;
        }

        $newMediaDto = clone $mediaDto;
        $newMediaDto->setId(null);
        $newMediaDto->setObjectKey($newItemId);
        $mapper = $this->getMapper();
        $newMediaId = $mapper->insertDto($newMediaDto);
        if(!$newMediaId) {
            return false;
        }


        $imageExtension = $mediaDto->getExtension();
        $fileType = $this->getTypeByExtension($imageExtension);
        $subFolder = $fileType === 'image' ? 'images' : 'files';
        $filePath = $this->defineFolder($mediaId, $subFolder) . '/' . $mediaId . '.' . $imageExtension;
        $destPath = $this->defineFolder($newMediaId, $subFolder) . '/' . $newMediaId . '.' . $imageExtension;
        if(file_exists($filePath)) {
            $copied = copy($filePath, $destPath);
            if(!$copied) {
                return false;
            }
        }

        if($fileType === 'image') {
            foreach($this->thumbOptions as $thumb => $options) {
                $filePath = $this->defineFolder($mediaId, $subFolder, $thumb) . '/' . $mediaId . '.' . $imageExtension;
                $destPath = $this->defineFolder($newMediaId, $subFolder, $thumb) . '/' . $newMediaId . '.' . $imageExtension;
                if(file_exists($filePath)) {
                    $copied = copy($filePath, $destPath);
                    if(!$copied) {
                        return false;
                    }
                }
            }
        }

        return true;
    }


    /**
     * remove media by given id, and corresponding folders
     *
     * @param int $id
     * @return bool
     */
    public function removeMediaById(int $id) {
        /** @var MediasDto $mediaDto */
        $mediaDto = $this->getItemById($id);
        if(!$mediaDto) {
            return false;
        }


        $imageExtension = $mediaDto->getExtension();
        $fileType = $this->getTypeByExtension($imageExtension);
        $subFolder = $fileType === 'image' ? 'images' : 'files';

        $filePath = $this->defineFolder($id, $subFolder) . '/' . $id . '.' . $imageExtension;
        if(file_exists($filePath)) {
            unlink($filePath);
        }

        if($fileType === 'image') {
            foreach($this->thumbOptions as $thumb => $options) {
                $filePath = $this->defineFolder($id, $subFolder, $thumb) . '/' . $id . '.' . $imageExtension;
                if(file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $this->deleteItemById($id);

        return true;
    }

    /**
     * get item all images urls
     *
     * @param $itemId
     * @param $itemType
     * @return array
     */
    public function getItemImagesUrlsAndDescriptions($itemId, $itemType){
        if(!$itemId) {
            return [];
        }
        $images = $this->getItemImages($itemId, $itemType);
        $result = [];
        foreach($images as $index => $image) {
            $folder = $image->getId() % NGS()->getDefinedValue('FOLDERS_COUNT_DELIMITER');
            $result[$index]['url']['original'] = NGS()->getDefinedValue('MY_HOST') . '/streamer/' . $image->getType() . 's/' .$image->getObjectType() .'/'. $image->getId();
            if(file_exists(NGS()->getDataDir() . '/' . $image->getType() . 's/thumbs/small/' . $folder .'/'. $image->getId() . '.' .$image->getExtension())){
                $result[$index]['url']['small'] = NGS()->getDefinedValue('MY_HOST') . '/streamer/' . $image->getType() . 's/' .$image->getObjectType() .'/'. $image->getId() . '?thumb=small';
            }
            if(file_exists(NGS()->getDataDir() . '/' . $image->getType() . 's/thumbs/medium/' . $folder .'/'. $image->getId() . '.' .$image->getExtension())){
                $result[$index]['url']['medium'] = NGS()->getDefinedValue('MY_HOST') . '/streamer/' . $image->getType() . 's/' .$image->getObjectType() .'/'. $image->getId() . '?thumb=medium';
            }
            if(file_exists(NGS()->getDataDir() . '/' . $image->getType() . 's/thumbs/big/' . $folder .'/'. $image->getId() . '.' .$image->getExtension())){
                $result[$index]['url']['big'] = NGS()->getDefinedValue('MY_HOST') . '/streamer/' . $image->getType() . 's/' .$image->getObjectType() .'/'. $image->getId() . '?thumb=big';
            }

            $result[$index]['description'] = $image->getDescription();

            if($image->getIsMain()) {
                $result[$index]['isMain'] = true;
            }

        }

        return $result;
    }


    /**
     * returns items images
     *
     * @param int $itemId
     * @param string $itemType
     * @return MediasDto[]
     */
    public function getItemImages(int $itemId, string $itemType) :array
    {
        $mapper = $this->getMapper();
        /** @var MediasDto[] $images */
        $images = $mapper->getItemImages($itemId, $itemType);
        return $images;
    }


    public function getItemAttachedFilesProperties($itemId, $itemType){
        $mapper = $this->getMapper();
        $files = $mapper->getItemAttachedFiles($itemId, $itemType);
        $result = [];
        foreach($files as $index => $file) {
            $result[$index]['id'] = $file->getId();
            $result[$index]['name'] = $file->getOriginalName();
            $result[$index]['description'] = $file->getDescription();
            $result[$index]['url'] = NGS()->getDefinedValue('MY_HOST') . '/streamer/' . $file->getType() . 's/' .$file->getObjectType() .'/'. $file->getId();
        }
        return $result;
    }

    public function updateImageTitle($id, $text){
        return MediasMapper::getInstance()->updateImageTitle($id, $text);
    }


    /**
     * @param $id
     * @param $type
     * @return string
     */
    public function getImageUrlByObjectKeyAndObjectType($id, $type):string {
        $mapper = $this->getMapper();
        if(empty($mapper->getItemImages($id, $type))){
            return '';
        }
        $image = $mapper->getItemImages($id, $type)[0];
        $id = $image->getId();

        if(!$id || !$type) {
            return '';
        }
        return NGS()->getDefinedValue('MY_HOST') . '/streamer/images/' . $type . '/' . $id;
    }


    /**
     * returns directory for given media
     *
     * @param $num
     * @param string $baseFolder
     * @param null $thumbFolder
     * @return string
     */
    private function defineFolder($num, $baseFolder = 'images', $thumbFolder = null)
    {
        $folderName = $num % NGS()->getDefinedValue('FOLDERS_COUNT_DELIMITER');
        if($thumbFolder) {
            if(!is_dir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/thumbs')) {
                mkdir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/thumbs');
            }
            if(!is_dir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/thumbs/' . $thumbFolder)) {
                mkdir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/thumbs/' . $thumbFolder);
            }
            $folderName = 'thumbs/' . $thumbFolder . '/' . $folderName;
        }

        if(!is_dir(NGS()->getDataDir('admin') . '/' .$baseFolder)) {
            mkdir(NGS()->getDataDir('admin') . '/' .$baseFolder);
        }
        if (!is_dir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/' . $folderName)) {
            mkdir(NGS()->getDataDir('admin') . '/' .$baseFolder . '/' . $folderName);
        }

        return NGS()->getDataDir('admin') . '/' .$baseFolder . '/' . $folderName;
    }


    private function removeFolder($folder)
    {
        if(is_dir($folder)){
            if (count(scandir($folder)) == 2) {
                rmdir($folder);
            }
        }

    }

    private function createImageThumbnails($src, $id, $extension) {
        foreach($this->thumbOptions as $thumbName => $option) {
            $folder = $this->defineFolder($id, 'images', $thumbName);       //todo: need to define file type, for example image and send 'images' to defineFolder
            $destination = $folder . '/' . $id . '.' . $extension;
            $this->createImageThumbnail($src, $destination, $option[0], $option[1]);
            $this->removeFolder($folder); //we do this because maybe createImageThumbnail will return null, and the created folder will stay empty
        }
    }

    private function createImageThumbnail($src, $dest, $width, $height) {
        $type = exif_imagetype($src);
        if (!$type || !self::IMAGE_HANDLERS[$type]) {
            return null;
        }
        $image = call_user_func(self::IMAGE_HANDLERS[$type]['load'], $src);
        if (!$image) {
            return null;
        }
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if($originalWidth < $width || ($originalHeight < $height)) {
            return null;
        }

        if ($originalWidth > $originalHeight) {
            $height = ($originalHeight / $originalWidth) * $width;
        }else {
            $height = $width;
            $width = floor($width * ($originalWidth / $originalHeight));
        }

        $thumbnail = imagecreatetruecolor($width, $height);
        if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {
            imagecolortransparent($thumbnail, imagecolorallocate($thumbnail, 0, 0, 0));

            if ($type == IMAGETYPE_PNG) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }
        }

        imagecopyresampled(
            $thumbnail,
            $image,
            0, 0, 0, 0,
            $width, $height,
            $originalWidth, $originalHeight
        );

        return call_user_func(self::IMAGE_HANDLERS[$type]['save'], $thumbnail, $dest, self::IMAGE_HANDLERS[$type]['quality']);
    }


    /**
     * this function first sets all columns in the rows of the current item to NULL and then sets the correct row to isMain
     *
     * @param $itemId
     * @param $tableName
     * @param $imageId
     * @throws \ngs\exceptions\DebugException
     */
    public function updateMainImageOfCurrentItem($itemId, $tableName, $imageId) {
        $mapper = $this->getMapper();
        $mapper->setMainImageToNullInAllRowsOfCurrentItem($itemId, $tableName);

        $mapper->updateField($imageId, 'is_main', 1);

    }




    //todo: need to check these functions (2 functions below). They were removed, but without them file remove or image remove works  wrong;
    public function removeImageFromFolder($id)

    {
        $imageExtension = MediasMapper::getInstance()->getMediaExtension($id);
        $fileAndItsThumbs = [
            'original' => ['folder' => $this->defineFolder($id, 'images'), 'path' => $this->defineFolder($id) . '/' . $id . '.' . $imageExtension],
            'smallThumb' => ['folder' => $this->defineFolder($id, 'images', 'small'), 'path' => $this->defineFolder($id, 'images', 'small') . '/' . $id . '.' . $imageExtension],
            'mediumThumb' => ['folder' => $this->defineFolder($id, 'images','medium'), 'path' => $this->defineFolder($id, 'images', 'medium') . '/' . $id . '.' . $imageExtension],
            'bigThumb' => ['folder' => $this->defineFolder($id, 'images','big'), 'path' => $this->defineFolder($id, 'images', 'big') . '/' . $id . '.' . $imageExtension]
        ];
        foreach ($fileAndItsThumbs as $properties) {
            if(is_file($properties['path'])) {
                unlink($properties['path']);
            }
            $this->removeFolder($properties['folder']);
        }

    }

    /**
     * @param $id
     */
    public function removeFileFromFolder($id)
    {
        $fileExtension = MediasMapper::getInstance()->getMediaExtension($id);
        if($fileExtension){

            $folder = $this->defineFolder($id, 'files');
            $filePath = $folder . '/' . $id . '.' . $fileExtension;
            if(is_file($filePath)){
                unlink($filePath);
            }
            $this->removeFolder($folder);
        }
    }

    //todo: till here


}