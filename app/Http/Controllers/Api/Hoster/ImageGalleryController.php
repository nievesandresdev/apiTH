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

    // public function upload_image(Request $request){
    //     ['hotel_id' => $hotel_id, 'type'=>$type, 'name_image'=>$name_image] = $request->all();
    //     $hotel = hotel::find($hotel_id);
    //     if (!$hotel) return;
    //     $customname = $name_image;
    //     if ($customname) {
    //         $customname = \Str::slug($customname, "_");
    //         $numbers_images_with_same_name = $hotel->gallery()->where('concept', $type)->where('name', 'like',  ['%' . $customname . '%'])->count();
    //         if ($numbers_images_with_same_name > 0) {
    //             $counter = $numbers_images_with_same_name > 1 ? $numbers_images_with_same_name + 1 : 1;
    //             $customname = "$customname"."_"."$counter";
    //         }
    //     }
    //     $path_img = saveImage($request->file, "gallery", $hotel->id, $type,true, $customname);
        
    //     return $path_img;
    // }

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
