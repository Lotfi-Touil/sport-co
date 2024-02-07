<?php

namespace App\Service;

use mysql_xdevapi\Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private $params; //recup infos service.yml

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $image,?string $folder = "",?int $width = 150, ?int $height = 150)
    {
        $file = md5(uniqid(rand(),true)) . '.webp'; //modif nom image

        $image_infos = getimagesize($image);

        if($image_infos === false){
            throw new Exception("Format d'image incorrect");
        }

        switch ($image_infos['mime']){
            case 'image/png' :
                $image_source = imagecreatefrompng($image);
                break;
            case 'image/jpeg':
                $image_source = imagecreatefromjpeg($image);
                break;
            case 'image/webp':
                $image_source = imagecreatefromwebp($image);
                break;
            default :
                throw new \Exception("Format d'image incorrect");
        }

        //recupere dimension
        $image_width = $image_infos[0];
        $image_height = $image_infos[1];

        //on check orientation image
        switch ($image_width <=> $image_height){
            case -1 :
                //portrait
                $squareSize = $image_width;
                $src_x=0;
                $src_y=($image_height-$squareSize)/2;
                break;
            case 0 :
                //carré
                $squareSize = $image_width;
                $src_x=0;
                $src_y=0;
                break;
            case 1:
                //paysage
                $squareSize = $image_height;
                $src_x=($image_height-$squareSize)/2;
                $src_y=0;
                break;
        }

        $resize_image = imagecreatetruecolor($width,$height);

        imagecopyresampled($resize_image,$image_source,0,0,$src_x,$src_y,$width,$height,$squareSize,$squareSize);


        $path = $this->params->get('image_directory') . $folder;

        //On crée le dossier si il existe pas, dont le fichier pour les minia
        if(!file_exists($path .'/mini/')){
            mkdir($path.'/mini/',0755,true);
        }

        imagewebp($resize_image,$path . '/mini/' . $width . 'x'. $height . '-' .$file);

        $image->move($path . '/' ,$file);

        return $file;
    }

    public function delete(string $file, ?string $folder = '',?int $width = 150, ?int $height = 150)
    {
        if($file !== 'default.webp'){
            $success = false;
            $path = $this->params->get('image_directory') . $folder;

            $mini = $path . '/mini/'. $width . 'x'. $height . '-' .$file;

            if(file_exists($mini)){
                unlink($mini);
                $success = true;
            }
             $original = $path . '/' . $file;

            if(file_exists($original)){
                unlink($original);
                $success = true;
            }
            return $success;

        }
        return false;
    }
}