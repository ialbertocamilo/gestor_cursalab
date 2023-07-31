<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignSummoneds extends Model
{
    protected $fillable = ['id', 'campaign_id', 'user_id', 'in_date', 'answer', 'candidate_state', 'state_sustent'];

    protected $casts = [
        'in_date' => 'datetime:d/m/Y',        
        'state_sustent' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function postulates() {
        return $this->hasMany(CampaignPostulates::class, 'summoned_id');
    }

    public function votations() {
        return $this->hasMany(CampaignVotations::class, 'summoned_id');
    }

    public function postulates_state_active() {
        return $this->hasMany(CampaignPostulates::class, 'summoned_id')
                    ->where('state', 1);
    }

    public function postulates_state_null() {
        return $this->hasMany(CampaignPostulates::class, 'summoned_id')
                    ->whereNull('state');
    }

    public function getCriterioValueUserCampaign() {

        $criterio_value = $this->user->criterion_values;
        
        if($criterio_value) {
            [ $value ] = $criterio_value;
            return $value['value_text'];
        }
        
        return $criterio_value;
    }

    protected function getSummonedAnswerCandidate($campaign_id, $user_id) {
        return self::select('answer','candidate_state')
                    ->where('campaign_id', $campaign_id)
                    ->where('user_id', $user_id)
                    ->first();
    }
    
    protected function search($request) {
        $q = self::query();

        if($request->criterio_value_id) {
            $q->whereRelation('user.criterion_values', 'id', $request->criterio_value_id); 
        }

        if($request->q) {
            $q->whereHas('user', function($q) use($request) {
                $q->where('name', 'like', "%$request->q%")
                  ->orWhere('surname', 'like', "%$request->q%")
                  ->orWhere('document', 'like', "%$request->q%");
            });
        }

        if($request->criterio_id) {
          $q->with(['user:id,name,document,lastname,surname,fullname' =>
                [
                    'criterion_values' => fn ($q) => $q->select('id', 'value_text')
                                                       ->where('criterion_id', $request->criterio_id), 
                    'criterion_user' => fn($q) => $q->select('criterion_value_id') 
                ]
            ]);
        }else {
            $q->with('user:id,name,document,lastname,surname,fullname');
        }

        if(is_string($request->active)) {
            $q->where('state_sustent', (int) $request->active );
        }

        $q->where('campaign_id', $request->campaign_id)
          ->withCount('postulates as total')
          ->withCount('postulates_state_active as approveds')
          ->withCount('postulates_state_null as pendings');

        if($request->sortBy) {
            $q->orderBy($request->sortBy, ($request->sortDesc) ? 'DESC': 'ASC');
        }else {
            $q->orderBy('total', 'DESC');
        }

        return $q->paginate($request->paginate);
    }

    protected function search_votations_votes($request) {   

        $q = self::query()
            ->select('id', 'campaign_id', 'user_id')
            ->with(['user:id,name,document,lastname,surname,fullname' =>
                [
                    'criterion_values' => fn ($q) => $q->select('id', 'value_text')
                                                       ->where('criterion_id', $request->criterio_id), 
                    'criterion_user' => fn($q) => $q->select('criterion_value_id') 
                ]
            ]);

        if($request->criterio_value_id) {
            $q->whereRelation('user.criterion_values', 'id', $request->criterio_value_id); 
        }

        if($request->criterio_id) {
            $q->whereRelation('user.criterion_values', 'criterion_id', $request->criterio_id); 
        }

        if($request->q) {
            $search = trim($request->q);
            $searchByDocument = is_numeric($search) && (strlen($search) >= 4);

            if($searchByDocument) {
                $q->whereHas('user', function($query) use($request) {
                    $query->Where('document', 'like', "$request->q%");
                });
            } else {
                $q->whereHas('user', function($query) use($request) {
                    $query->where('name', 'like', "%$request->q%")
                          ->orWhere('lastname', 'like', "%$request->q%")
                          ->orWhere('surname', 'like', "%$request->q%");
                });
            }
        }

        $q->withCount('votations as votes')
          ->where('campaign_id', $request->campaign_id)
          ->where('state_sustent', 1)
          ->where('candidate_state', 1);

        $q->orderBy('votes', 'DESC');

        return $q->paginate($request->paginate);
    }


    protected function candidates_votes_criterio($campaign_id, $criterion_id, $criterio_value_id) {   

        $q = self::query()
            ->whereRelation('user.criterion_values', 'criterion_id', $criterion_id)
            ->whereRelation('user.criterion_values', 'id', $criterio_value_id)
            ->withCount('votations as votes')
            ->where('campaign_id', $campaign_id)
            ->where('state_sustent', 1)
            ->where('candidate_state', 1);

        return $q->get()->sum('votes');
    }

    protected function search_candidates_api($request) {

        $q = self::query()
                 ->select('id', 'campaign_id', 'user_id')
                 ->with('user:id,name,document,lastname,surname,fullname');

        if($request->criterio_value_id) {
            $q->whereRelation('user.criterion_values', 'id', $request->criterio_value_id); 
        }

        if($request->criterio_id) {
            $q->whereRelation('user.criterion_values', 'criterion_id', $request->criterio_id); 
        }

        if($request->q) {
            $search = trim($request->q);
            $searchByDocument = is_numeric($search) && (strlen($search) >= 4);

            if($searchByDocument) {
                $q->whereHas('user', function($query) use($request) {
                    $query->Where('document', 'like', "$request->q%");
                });
            } else {
                $q->whereHas('user', function($query) use($request) {
                    $query->where('name', 'like', "%$request->q%")
                          ->orWhere('lastname', 'like', "%$request->q%")
                          ->orWhere('surname', 'like', "%$request->q%");
                });
            }
        }

        $q->where('campaign_id', $request->campaign_id)
          ->whereNotIn('user_id', [ $request->user_id ])
          ->where('state_sustent', 1)
          ->where('candidate_state', 1)
          ->orderBy('id', 'DESC');

        return $q->paginate($request->paginate);
    }

    protected function search_votations_api($request) {
        $q = self::query()
                 ->select('id', 'campaign_id', 'user_id')
                 ->with('user:id,name,document,lastname,surname,fullname')
                 ->whereRelation('votations', 'user_id', $request->user_id);

        if($request->q) {
            $search = trim($request->q);
            $searchByDocument = is_numeric($search) && (strlen($search) >= 4);

            if($searchByDocument) {
                $q->whereHas('user', function($query) use($request) {
                    $query->Where('document', 'like', "$request->q%");
                });
            } else {
                $q->whereHas('user', function($query) use($request) {
                    $query->where('name', 'like', "%$request->q%")
                          ->orWhere('lastname', 'like', "%$request->q%")
                          ->orWhere('surname', 'like', "%$request->q%");
                });
            }
        }

        $q->where('campaign_id', $request->campaign_id)
          ->orderBy('id', 'DESC');

        return $q->paginate($request->paginate);
    }


    protected function search_api($request) {
        $q = self::query()->select('id','campaign_id','user_id');

        if($request->criterio_value_id) {
            $q->whereRelation('user.criterion_values', 'id', $request->criterio_value_id); 
        }

        if($request->q) {
            $search = trim($request->q);
            $searchByDocument = is_numeric($search) && (strlen($search) >= 4);

            if($searchByDocument) {
                $q->whereHas('user', function($query) use($request) {
                    $query->Where('document', 'like', "$request->q%");
                });
            } else {
                $q->whereHas('user', function($query) use($request) {
                    $query->where('name', 'like', "%$request->q%")
                          ->orWhere('surname', 'like', "%$request->q%");
                });
            }
        }

        if($request->criterio_id) {
          $q->with(['user:id,name,document,lastname,surname,fullname' =>
                [
                    'criterion_values' => fn($q) => $q->select('id', 'value_text')
                                                       ->where('criterion_id', $request->criterio_id), 
                    'criterion_user' => fn($q) => $q->select('criterion_value_id') 
                ]
            ]);
        }else {
            $q->with('user:id,name,document,lastname,surname,fullname');
        }

        $q->where('campaign_id', $request->campaign_id)
          ->whereNotIn('user_id', [$request->user_id])
          ->withCount(['postulates as state_send' => function($query) use($request) {
                $query->where('user_id', $request->user_id);
          }]);

        if(is_string($request->active)) {
            $state = (int) $request->active;

            if($state) $q->having('state_send', '>', 0);
            else $q->having('state_send', '=', 0);
        }

        $q->orderBy('state_send', 'DESC');

        return $q->paginate($request->paginate);
    }

    protected function search_votations_ranking_api($request) {   

        $q = self::query()
            ->select('id', 'campaign_id', 'user_id')
            ->with('user:id,name,document,lastname,surname,fullname');

        if($request->criterio_value_id) {
            $q->whereRelation('user.criterion_values', 'id', $request->criterio_value_id); 
        }

        if($request->criterio_id) {
            $q->whereRelation('user.criterion_values', 'criterion_id', $request->criterio_id); 
        }

        if($request->q) {
            $search = trim($request->q);
            $searchByDocument = is_numeric($search) && (strlen($search) >= 4);

            if($searchByDocument) {
                $q->whereHas('user', function($query) use($request) {
                    $query->Where('document', 'like', "$request->q%");
                });
            } else {
                $q->whereHas('user', function($query) use($request) {
                    $query->where('name', 'like', "%$request->q%")
                          ->orWhere('lastname', 'like', "%$request->q%")
                          ->orWhere('surname', 'like', "%$request->q%");
                });
            }
        }

        $q->withCount('votations as votes')
          ->where('campaign_id', $request->campaign_id)
          ->where('state_sustent', 1)
          ->where('candidate_state', 1);

        return $q->paginate($request->paginate);
    }

    protected function candidates_votes($request) {

        $q = self::query()
                 ->select('id', 'campaign_id', 'user_id')
                 ->with(['user:id,name,document,lastname,surname,fullname' =>
                        [
                            'criterion_values' => fn($q) => $q->select('id', 'value_text')
                                                       ->where('criterion_id', $request->criterio_id), 
                            'criterion_user' => fn($q) => $q->select('criterion_value_id') 
                        ]
                ])
                ->whereRelation('user.criterion_values', 'criterion_id', $request->criterio_id); 

        $q->with(['votations' => ['user:id,name,document,lastname,surname'] ])
          ->where('campaign_id', $request->campaign_id)
          ->where('state_sustent', 1)
          ->where('candidate_state', 1)
          ->withCount('votations as votes');

        return $q->get();
    }

    protected function saveStateCandidateSustent($summoned_id, $candidate_state, $sustent_state) {

        $summoned = self::find($summoned_id);

        if(!is_null($candidate_state)) {
            $summoned->candidate_state = (bool) $candidate_state;
        }

        $summoned->state_sustent = $sustent_state;
        $summoned->save();
    }
}
