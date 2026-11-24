<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EstudianteController extends Controller
{
    // Listar estudiantes según el rol del usuario.
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Estudiante::class);

        $user = $request->user();
        $query = Estudiante::with(['carrera', 'sede']);

        if ($user->hasRole('administrador')) {
            // El administrador puede consultar todos.
        } elseif ($user->hasAnyRole(['profesor', 'coordinador'])) {
            abort_if($user->carrera_id === null, 403);

            $query->where('carrera_id', $user->carrera_id);
        } elseif ($user->hasRole('estudiante')) {
            $query->where('user_id', $user->id);
        } else {
            abort(403);
        }

        return response()->json($query->paginate(10));
    }

    // Registrar un nuevo estudiante.
    public function store(Request $request)
    {
        Gate::authorize('create', Estudiante::class);

        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                'unique:estudiantes,user_id',
            ],
            'carrera_id' => [
                'required',
                'integer',
                'exists:carreras,id',
            ],
            'sede_id' => [
                'required',
                'integer',
                'exists:sedes,id',
            ],
            'cedula' => [
                'required',
                'string',
                'max:20',
                'unique:estudiantes,cedula',
            ],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo_institucional' => [
                'required',
                'email',
                'max:255',
                'unique:estudiantes,correo_institucional',
            ],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $usuario = User::findOrFail($data['user_id']);

        if (
            ! $usuario->hasRole('estudiante') ||
            $usuario->carrera_id !== (int) $data['carrera_id'] ||
            $usuario->sede_id !== (int) $data['sede_id']
        ) {
            throw ValidationException::withMessages([
                'user_id' => [
                    'El usuario debe ser estudiante y coincidir en carrera y sede.',
                ],
            ]);
        }

        $estudiante = Estudiante::create($data);

        return response()->json(
            $estudiante->load(['carrera', 'sede']),
            201
        );
    }

    // Consultar un estudiante específico.
    public function show(Estudiante $estudiante)
    {
        Gate::authorize('view', $estudiante);

        return response()->json(
            $estudiante->load(['carrera', 'sede'])
        );
    }

    // Actualizar información de un estudiante.
    public function update(
        Request $request,
        Estudiante $estudiante
    ) {
        Gate::authorize('update', $estudiante);

        $data = $request->validate([
            'user_id' => ['missing'],
            'carrera_id' => ['missing'],
            'sede_id' => ['missing'],
            'cedula' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('estudiantes', 'cedula')
                    ->ignore($estudiante->id),
            ],
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'apellido' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'correo_institucional' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'estudiantes',
                    'correo_institucional'
                )->ignore($estudiante->id),
            ],
            'activo' => ['sometimes', 'required', 'boolean'],
        ]);

        $estudiante->update($data);

        return response()->json(
            $estudiante->load(['carrera', 'sede'])
        );
    }

    // Eliminar el registro académico.
    public function destroy(Estudiante $estudiante)
    {
        Gate::authorize('delete', $estudiante);

        $estudiante->delete();

        return response()->json([
            'message' => 'Estudiante eliminado correctamente',
        ]);
    }
}
