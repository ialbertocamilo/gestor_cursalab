<?php

namespace App\Console\Commands;

use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

use Illuminate\Console\Command;
use DB;

class RevokeImpersonationAccessTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:revoke-impersonation-access';

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

        $tokens = DB::table('oauth_access_tokens')
                        ->where('name', 'accessTokenImpersonation')
                        ->where('created_at', '<=', now()->subMinutes(60))
                        ->get();

        $bar = $this->output->createProgressBar($tokens->count());

        foreach ($tokens as $token) {
            
            $tokenRepository->revokeAccessToken($token->id);

            $bar->advance();
        }

        $bar->finish();
    }
}
