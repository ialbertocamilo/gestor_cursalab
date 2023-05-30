<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function search(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }

    public function store(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }
    
    public function update(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }
            
    public function status(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }
    
    public function delete(Request $request) 
    {
        $current = $request->all();
        return [__function__, __class__, $current];
    }
}
