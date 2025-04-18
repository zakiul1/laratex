<?php
namespace App\Http\Controllers\Admin;
use App\Contracts\PluginLifecycleInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plugin;

class HookController extends Controller
{
    public function index()
    {
        $actions = hooks()->actionsByPlugin();
        $filters = hooks()->filtersByPlugin();

        return view('admin.hooks.index', compact('actions', 'filters'));
    }

}