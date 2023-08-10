<?php

namespace App\Console\Commands;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use App\Models\User;

use Illuminate\Console\Command;
use DB;

class RevokeUsersAccessTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:revoke-users-access';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $users_id = User::whereNotNull('secret_key')->whereNotNull('email_gestor')->get()->pluck('id')->toArray();

        $max_minutes = config('session.app_lifetime');

        $tokens = DB::table('oauth_access_tokens')
                        ->where('name', 'accessToken')
                        ->whereNotIn('user_id', $users_id)
                        ->where('revoked', 0)
                        ->where('created_at', '<=', now()->subMinutes($max_minutes))
                        ->get();

        $bar = $this->output->createProgressBar($tokens->count());

        foreach ($tokens as $token) {
            
            $tokenRepository->revokeAccessToken($token->id);

            $bar->advance();
        }

        $bar->finish();
    }
}
