<?php

namespace App\Models;

use App\Models\Taxonomy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SectionUpload extends BaseModel
{
    protected $table = 'section_uploads';
    protected $fillable = ['user_id', 'section_id', 'type_id', 'fields', 'file', 'file_name'];

    public $defaultRelationships = [
        'section_id' => 'section',
        'type_id' => 'type',
        'user_id' => 'user'
    ];

    public function user() 
    {
        return $this->belongsTo(user::class, 'user_id');
    }

    public function section() 
    {
        return $this->belongsTo(Taxonomy::class, 'section_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public static function storeRequestLog($request, $codes) {

        $file = $request->file('file');
        $fields = $request->process; // === proceso en masivo ===

        [ 'code_section' => $code_section, 
          'code_type' => $code_type ] = $codes;

        $section = Taxonomy::where('group', 'upload')
                            ->where('type', 'section')
                            ->where('code', $code_section)
                            ->first();

        $type = Taxonomy::where('group', 'system')
                        ->where('type', 'action')
                        ->where('code', $code_type)
                        ->first();

        $ext = $file->getClientOriginalExtension();
        $namewithextension = $file->getClientOriginalName();
        $name = Str::slug(explode('.', $namewithextension)[0]);
        $name = Str::of($name)->limit(200);

        // === fileName ===
        $str_random = Str::random(15);
        $workspace_code = 'wrkspc-' . (get_current_workspace()->id ?? 'x');
        $name = $workspace_code . '-' . $name . '-' . date('YmdHis') . '-' . $str_random;
        $fileName = $name . '.' . $ext;
        // === fileName ===

        // === filePathLocation ===
        $path = 'uploadlogs/'.$fileName;
        $result = Storage::disk('s3')->put($path, file_get_contents($file));
        $location = get_media_url($path);
        // === filePathLocation ===

        $data = [
            'user_id' => auth()->user()->id,
            'section_id' => $section->id,
            'fields' => $fields,
            'type_id' => $type->id,
            'file' => $location,
            'file_name' => $namewithextension
        ];        

        if($result) {
            // info('create_data');
            self::create($data);
        } 
    }
}
