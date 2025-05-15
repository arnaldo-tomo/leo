<?php

namespace App\Http\Controllers;

use App\Models\AuthorizedPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AuthorizedPersonController extends Controller
{
    public function index()
    {
        $persons = AuthorizedPerson::all();
        return view('authorized.index', compact('persons'));
    }

    public function create()
    {
        return view('authorized.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'face_descriptor' => 'required|json',
            'access_level' => 'required|string|in:standard,admin,restricted',
            'notes' => 'nullable|string',
            'photo_data' => 'required|string',
        ]);

        // Decodificar e salvar a imagem base64
        $image = $request->photo_data;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time() . '.png';
        Storage::disk('public')->put('photos/' . $imageName, base64_decode($image));

        AuthorizedPerson::create([
            'name' => $validated['name'],
            'face_descriptor' => $validated['face_descriptor'],
            'access_level' => $validated['access_level'],
            'notes' => $validated['notes'],
            'photo_path' => 'photos/' . $imageName,
            'active' => true
        ]);

        return redirect()->route('authorized.index')->with('success', 'Pessoa autorizada adicionada com sucesso!');
    }

    public function edit(AuthorizedPerson $authorizedPerson)
    {
        return view('authorized.edit', compact('authorizedPerson'));
    }

    public function update(Request $request, AuthorizedPerson $authorizedPerson)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'access_level' => 'required|string|in:standard,admin,restricted',
            'notes' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $authorizedPerson->update($validated);

        // Atualizar foto se fornecida
        if ($request->has('photo_data') && !empty($request->photo_data)) {
            // Remover foto antiga se existir
            if ($authorizedPerson->photo_path) {
                Storage::disk('public')->delete($authorizedPerson->photo_path);
            }

            // Salvar nova foto
            $image = $request->photo_data;
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = time() . '.png';
            Storage::disk('public')->put('photos/' . $imageName, base64_decode($image));

            $authorizedPerson->update(['photo_path' => 'photos/' . $imageName]);
        }

        return redirect()->route('authorized.index')->with('success', 'Pessoa autorizada atualizada com sucesso!');
    }

    public function destroy(AuthorizedPerson $authorizedPerson)
    {
        // Remover foto se existir
        if ($authorizedPerson->photo_path) {
            Storage::disk('public')->delete($authorizedPerson->photo_path);
        }

        $authorizedPerson->delete();
        return redirect()->route('authorized.index')->with('success', 'Pessoa autorizada removida com sucesso!');
    }

    public function getAll()
    {
        $persons = AuthorizedPerson::where('active', true)->get(['id', 'name', 'face_descriptor']);
        return response()->json($persons);
    }
}