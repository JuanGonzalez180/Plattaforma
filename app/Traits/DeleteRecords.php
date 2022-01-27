<?php

namespace App\Traits;

use App\Models\Tags;
use App\Models\Image;
use App\Models\Files;
use App\Models\Remarks;
use App\Models\Interests;
use App\Models\QueryWall;
use App\Models\Notifications;
use App\Models\RegistrationPayments;
use Illuminate\Support\Facades\Storage;

trait DeleteRecords
{
    protected $routeFile = 'public/';

    //borra registros de archivos y los archivos guardados en el servidor.
    public function deleteFiles($array_id, $classModel)
    {
        $files  = Files::whereIn('filesable_id', $array_id)
            ->where('filesable_type', $classModel)
            ->get();

        foreach ($files as $file) {
            Storage::disk('local')->delete($this->routeFile . $file->url);
            $file->delete();
        }
    }

    //borra los registros de las imagenes y los archivos guardados en el servidor.
    public function deleteImage($array_id, $classModel)
    {
        $images  = Image::whereIn('imageable_id', $array_id)
            ->where('imageable_type', $classModel)
            ->get();

        foreach ($images as $image) {
            Storage::disk('local')->delete($this->routeFile . $image->url);
            //elimina la imagen
            Image::where('imageable_id', $image->imageable_id)
                ->where('imageable_type', $image->imageable_type)
                ->delete();
        }
    }

    //borra registos de etiquetas
    public function deleteTags($array_id, $classModel)
    {
        Tags::whereIn('tagsable_id', $array_id)
            ->where('tagsable_type', $classModel)
            ->delete();
    }

    //borra registro de notificaciones
    public function deleteNotifications($array_id, $classModel)
    {
        Notifications::whereIn('notificationsable_id', $array_id)
            ->where('notificationsable_type', $classModel)
            ->delete();
    }

    //borra el registro de las reseñas
    public function deleteRemarks($array_id, $classModel)
    {
        Remarks::whereIn('remarksable_id', $array_id)
            ->where('remarksable_type', $classModel)
            ->delete();
    }

    public function deleteInterests($array_id, $classModel)
    {
        Interests::whereIn('interestsable_id', $array_id)
            ->where('interestsable_type', $classModel)
            ->delete();
    }

    //borra registros de muro de consultas
    public function deleteQueryWall($array_id, $classModel)
    {
        QueryWall::whereIn('querysable_id', $array_id)
            ->where('querysable_type', $classModel)
            ->delete();
    }

    //Elimina los registros de facturación
    public function deleteAllRegistPayment($advertisings, $modelClass)
    {
        RegistrationPayments::whereIn('paymentsable_id', $advertisings)
            ->where('paymentsable_type', $modelClass)
            ->delete();
    }
}
