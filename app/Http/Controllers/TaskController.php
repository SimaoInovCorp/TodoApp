<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $filters = $this->sanitizeFilters($request);

        $tasksQuery = Task::query()
            ->forUser($user)
            ->tap(fn ($query) => $query->applyFilters($filters))
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderBy('due_date')
            ->latest('created_at');

        $tasks = TaskResource::collection($tasksQuery->get())->resolve();

        $baseQuery = Task::query()->forUser($user);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'completed' => (clone $baseQuery)->where('status', true)->count(),
        ];

        $stats['pending'] = $stats['total'] - $stats['completed'];

        return Inertia::render('Dashboard', [
            'tasks' => $tasks,
            'filters' => $filters,
            'stats' => $stats,
            'priorities' => Task::PRIORITIES,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(TaskRequest $request): RedirectResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('success', "Task '{$task->title}' created successfully.");
    }

    /**
     * Update the specified task.
     */
    public function update(TaskUpdateRequest $request, Task $task): RedirectResponse
    {
        $this->ensureOwner($task, $request->user());

        $task->fill($request->validated());
        $task->save();

        return redirect()
            ->route('tasks.index')
            ->with('success', "Task '{$task->title}' updated successfully.");
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->ensureOwner($task, $request->user());

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', "Task '{$task->title}' removed successfully.");
    }

    /**
     * Ensure the authenticated user owns the task.
     */
    private function ensureOwner(Task $task, User $user): void
    {
        abort_if($task->user_id !== $user->id, HttpResponse::HTTP_FORBIDDEN);
    }

    /**
     * Validate and normalize filter inputs.
     *
     * @return array{status: string, priority: string|null, due_date: string|null, search: string|null}
     */
    private function sanitizeFilters(Request $request): array
    {
        $status = $request->string('status', 'pending')->toString();
        $priority = $request->string('priority')->toString();
        $dueDate = $request->string('due_date')->toString();
        $search = $request->string('search')->toString();

        $status = in_array($status, ['pending', 'completed', 'all'], true) ? $status : 'all';
        $priority = in_array($priority, Task::PRIORITIES, true) ? $priority : null;
        $dueDate = $dueDate !== '' ? $dueDate : null;
        $search = $search !== '' ? $search : null;

        return [
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate,
            'search' => $search,
        ];
    }
}
