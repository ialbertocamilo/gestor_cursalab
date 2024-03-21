<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
class MediaTema extends BaseModel
{
    protected $table = 'media_topics';

    protected $fillable = [
        'topic_id', 'title', 'value', 'embed', 'downloadable', 'position', 'type_id','ia_convert'
    ];

    protected $casts = [
        'embed' => 'boolean',
        'downloadable' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function getSizeByCourse($course){
        return MediaTema::select('media.size')->join('media','media.file','media_topics.value')->whereHas('topic', function($q) use ($course) {
            $q->where('course_id', $course->id)->where('active',ACTIVE);
        })->groupBy('media.file')->get()->sum('size');
    }

    protected function dataSizeCourse($course,$has_offline=null,$size_limit_offline=null){
        $has_offline = $has_offline  ?? get_current_workspace()->functionalities()->where('code','course-offline')->first();
        if(!$has_offline || !$course->is_offline){
            return [
                'is_offline' => false,
                'limit'=>0 ,
                'has_space'=> true,
                'sum_size_topics' => 0
            ];
        }
        $sum_size_topics = MediaTema::getSizeByCourse($course);
        $limit_offline = Ambiente::getSizeLimitOffline(false,$size_limit_offline);
        return [
            'is_offline' => $course->is_offline,
            'limit'=> $limit_offline['size_limit_offline'].$limit_offline['size_unit'],
            'has_space'=> $sum_size_topics <= $limit_offline['size_in_kb'],
            'sum_size_topics' => formatSize(kilobytes:$sum_size_topics,parsed:false)
        ];
    }

    protected function downloadMedia($media_id){
        $media_tema = self::where('id', $media_id)->select('value', 'title')->first();
        if (!$media_tema) {
            return $this->error('No se encontró el archivo multimedia');
        }
        
        $encryptionKey = 'course-offline'; // Clave de cifrado

        // Configurar el cliente de AWS S3
        $config = config('filesystems.disks.s3');
        $bucket = $config['bucket'];

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
            'endpoint'    => 'https://sfo2.digitaloceanspaces.com',
            'options' => [
                'CacheControl' => 'max-age=25920000, no-transform, public',
            ]
        ]);


        // Descargar el objeto desde DigitalOcean Spaces
        $result = $s3Client->getObject([
            'Bucket' => $bucket, // Reemplaza con el nombre de tu bucket en S3
            'Key' => $config['root'].'/'.$media_tema->value,
        ]);
        // Obtener el contenido del objeto
        $objectContent = $result['Body']->getContents();
        // Cifrar el contenido del archivo
        $bytes = hex2bin('3eafb3e2c7d36af6aeb1f36b0ae31768');
        // $bytes = random_bytes(16);
        $encryptedData = openssl_encrypt($objectContent, 'aes-256-cbc', $encryptionKey, 0, $bytes);
        // Guardar el archivo cifrado en un archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'encrypted_media_');
        file_put_contents($tempFile, $encryptedData);

        // Retornar el archivo temporal como una descarga
        return response()->download($tempFile, $media_tema->title . '.enc')->deleteFileAfterSend(true);
    }
}
