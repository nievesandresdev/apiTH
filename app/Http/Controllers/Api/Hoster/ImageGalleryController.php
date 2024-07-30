<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\ImageGallery;
use App\Models\hotel;

use Illuminate\Support\Str;
use App\Utils\Enums\EnumResponse;

class ImageGalleryController extends Controller
{
    public function getAll(Request $request) {
        try {

            ['search' => $search] = $request->all();

            $hotelModel = $request->attributes->get('hotel');

            $q = $hotelModel->gallery();
            if ($search) {
                $q = $q->where('name','like','%'.$search.'%');
            }

            $imagesGallery = $q->orderBy('created_at', 'desc')->get();
            // return $imagesGallery;
            $imagesGalleryPlaces = $imagesGallery->filter(function($img){
                return $img['concept'] == 'image-place';
            })->values();
            $imagesGalleryHotel = $imagesGallery->filter(function($img){
                return $img['concept'] == 'image-hotel';
            })->values();
            $data = [
                'images_gallery_places' => $imagesGalleryPlaces,
                'images_gallery_hotel' => $imagesGalleryHotel,
            ];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }

    }

    public function upload(Request $request){
        try {
            $hotelModel = $request->attributes->get('hotel');
            ['type'=>$type, 'name_image'=>$nameImage] = $request->all();
            if (!$hotelModel) return;
            $customname = $nameImage;
            if ($customname) {
                $customname = \Str::slug($customname, "_");
                $numbersImagesWithSameName = $hotelModel->gallery()->where('concept', $type)->where('name', 'like',  ['%' . $customname . '%'])->count();
                if ($numbersImagesWithSameName > 0) {
                    $counter = $numbersImagesWithSameName > 1 ? $numbersImagesWithSameName + 1 : 1;
                    $customname = "$customname"."_"."$counter";
                }
            } 
            $pathImg = saveImage($request->file, "gallery", $hotelModel->id, $type, true, $customname);
            $data = ["url" => $pathImg, 'input' => $request->all()];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.upload');
        }
    }

    public function deleteBulk(Request $request){
        try {
            $ids_delete = $request->ids_delete ?? [];
            if (!$ids_delete || count($ids_delete) == 0) return $ids_delete;
            $data = ImageGallery::whereIn('id',$request->ids_delete)->delete();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
            //
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteBulk');
        }
    }

}
