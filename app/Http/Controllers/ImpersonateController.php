<?php

namespace App\Http\Controllers;
// namespace Lab404\Impersonate\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lab404\Impersonate\Services\ImpersonateManager;
use App\Models\Workspace;
use App\Models\User;

class ImpersonateController extends Controller
{
    /** @var ImpersonateManager */
    protected $manager;

    /**
     * ImpersonateController constructor.
     */
    public function __construct()
    {
        $this->manager = app()->make(ImpersonateManager::class);
        
        // $guard = $this->manager->getDefaultSessionGuard();
        // $this->middleware('auth:' . $guard)->only('take');
    }

    /**
     * @param int         $id
     * @param string|null $guardName
     * @return  RedirectResponse
     * @throws  \Exception
     */
    // public function take(Request $request, $id, $guardName = null)
    public function take(Request $request, $value)
    {
        $field = $request->field ?? 'id';
        // $guardName = $guardName ?? $this->manager->getDefaultSessionGuard();
        $guardName = $this->manager->getCurrentAuthGuardName();

        $fieldValue = $field == 'id' ? $request->user()->getAuthIdentifier() : $request->user()->$field ;

        if (!$fieldValue) {
            abort(403);
        }

        // Cannot impersonate yourself
        // if ($id == $request->user()->getAuthIdentifier() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
        if ($value == $fieldValue) {
            // dd('AAAAAA');
            abort(403);
        }

        // Cannot impersonate again if you're already impersonate a user
        if ($this->manager->isImpersonating()) {
            // dd('BBBBBB');
            abort(403);
        }

        if (!$request->user()->canImpersonate()) {
            // dd('CCCCC');
            abort(403);
        }

        if ($field == 'id') {

            $userToImpersonate = $this->manager->findUserById($id, $guardName);
            
        } else {

            $userToImpersonate = User::findUserToImpersonate($value, $field, $guardName);
        }

        if ($userToImpersonate->canBeImpersonated()) {
            if ($this->manager->take($request->user(), $userToImpersonate, $guardName)) {

                if ($guardName == 'web') {

                    $workspaces =  Workspace::loadUserWorkspaces($userToImpersonate->id);

                    session(['workspace' => $workspaces[0]]);
                    
                    $takeRedirect = $this->manager->getTakeRedirectTo();

                    if ($takeRedirect !== 'back') {
                        return redirect()->to($takeRedirect);
                    }

                } else {
                    return true;
                }
            }
        }

        return $guardName == 'web' ? redirect()->back() : false;
    }

    /**
     * @return RedirectResponse
     */
    public function leave()
    {
        $guardName = $this->manager->getCurrentAuthGuardName();

        if (!$this->manager->isImpersonating()) {
            abort(403);
        }

        $this->manager->leave();

        if ($guardName == 'web') {

            $workspaces =  Workspace::loadUserWorkspaces(auth()->user()->id);

            session(['workspace' => $workspaces[0]]);

            $leaveRedirect = $this->manager->getLeaveRedirectTo();

            if ($leaveRedirect !== 'back') {
                return redirect()->to($leaveRedirect);
            }

            return redirect()->back();
        }

        return true;
    }
}
