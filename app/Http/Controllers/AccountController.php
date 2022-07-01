<?php

namespace App\Http\Controllers;

use App\Models\Zoom;
use App\Models\Account;
use App\Models\Taxonomy;

use Illuminate\Http\Request;
use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;
// use App\Http\Controllers\ZoomApi;

class AccountController extends Controller
{
    public function search(Request $request)
    {
        // $x = 8/0;
        // CentuaZoom::search();
        $accounts = Account::search($request);

        AccountResource::collection($accounts);

        return $this->success($accounts);
    }

    public function getListSelects()
    {
        $services = Taxonomy::getSelectData('account', 'service');

        return $this->success(get_defined_vars());
    }

    public function create()
    {
        $services = Taxonomy::getSelectData('account', 'service');
        $plans = Taxonomy::getSelectData('account', 'plan');
        $types = Taxonomy::getSelectData('meeting', 'type');

        return $this->success(get_defined_vars());
    }

    public function store(AccountRequest $request)
    {
        $account = Account::create($request->validated());

        return $this->success(['msg' => 'Cuenta creada correctamente.']);
    }

    public function edit(Account $account)
    {
        $services = Taxonomy::getSelectData('account', 'service');
        $plans = Taxonomy::getSelectData('account', 'plan');
        $types = Taxonomy::getSelectData('meeting', 'type');

        $account->load('service', 'plan', 'type');

        return $this->success(get_defined_vars());
    }

    public function update(Account $account, AccountRequest $request)
    {
        $account->update($request->validated());

        return $this->success(['msg' => 'Cuenta editada correctamente.']);
    }

    public function status(Account $account, Request $request)
    {
        $account->update(['active' => !$account->active]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function generarToken(Account $account, Request $request)
    {
        $account->generateJWT();
        $account->regenerateZoomTokens();

        return $this->success(['msg' => 'Tokens SDK y ZAK generados correctamente.']);
    }

    public function destroy(Account $account)
    {
        $account->delete();

        return $this->success(['msg' => 'Cuenta eliminada correctamente.']);
    }
}
