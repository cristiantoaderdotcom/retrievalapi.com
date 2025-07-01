<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
class WorkspaceSwitcherController extends Controller {
	public function __invoke(Request $request, string $uuid) {
		$workspace = Workspace::query()
			->where('uuid', $uuid)
			->where('user_id', $request->user()->id)
			->first();

		if ($workspace === null)
			return response()->json(['success' => false, 'message' => 'Workspace not found'], 404);

		$request->session()->put('workspace', $workspace->toArray());

		return redirect()->route('app.workspace.show', $workspace->uuid);
	}
}
