<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    BadgeCheck,
    CalendarClock,
    CheckCircle2,
    Clock4,
    Filter,
    Info,
    Pencil,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Task = {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    priority: 'high' | 'medium' | 'low';
    status: boolean;
    overdue: boolean;
};

type Filters = {
    status: 'pending' | 'completed' | 'all';
    priority: Task['priority'] | null;
    due_date: string;
    search: string;
};

const props = defineProps<{
    tasks: Task[];
    filters: Filters;
    stats: { total: number; completed: number; pending: number };
    priorities: Task['priority'][];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
    },
];

const statusOptions: Array<{ value: Filters['status']; label: string }> = [
    { value: 'all', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'completed', label: 'Completed' },
];

const priorityLabels: Record<Task['priority'], string> = {
    high: 'High',
    medium: 'Medium',
    low: 'Low',
};

const priorityColors: Record<Task['priority'], string> = {
    high: 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-500/20 dark:text-rose-100',
    medium: 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-500/20 dark:text-amber-100',
    low: 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-100',
};

const filterForm = reactive<Filters>({
    status: props.filters.status ?? 'pending',
    priority: props.filters.priority ?? null,
    due_date: props.filters.due_date ?? '',
    search: props.filters.search ?? '',
});

const applyFilters = useDebounceFn(() => {
    router.get('/tasks', filterForm, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        only: ['tasks', 'filters', 'stats'],
    });
}, 300);

watch(filterForm, () => applyFilters(), { deep: true });

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const detailDialogOpen = ref(false);
const activeTask = ref<Task | null>(null);

const createForm = useForm({
    title: '',
    description: '',
    due_date: '',
    priority: props.priorities[0] ?? 'medium',
});

const editForm = useForm({
    title: '',
    description: '',
    due_date: '',
    priority: props.priorities[0] ?? 'medium',
    status: false,
});

const sortedTasks = computed(() => props.tasks ?? []);

function formatDate(date?: string | null): string {
    if (!date) return 'No date';

    return new Intl.DateTimeFormat('en-US', {
        day: '2-digit',
        month: 'short',
    }).format(new Date(date));
}

function toggleStatus(task: Task) {
    router.put(
        `/tasks/${task.id}`,
        { status: !task.status },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['tasks', 'filters', 'stats'],
        },
    );
}

function openEdit(task: Task) {
    activeTask.value = task;
    editForm.title = task.title;
    editForm.description = task.description ?? '';
    editForm.due_date = task.due_date ?? '';
    editForm.priority = task.priority;
    editForm.status = task.status;
    editDialogOpen.value = true;
}

function openDetails(task: Task) {
    activeTask.value = task;
    detailDialogOpen.value = true;
}

function submitCreate() {
    createForm.post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
        },
    });
}

function submitEdit() {
    if (!activeTask.value) return;

    editForm.put(`/tasks/${activeTask.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editDialogOpen.value = false;
            activeTask.value = null;
        },
    });
}

function deleteTask(task: Task) {
    if (!confirm('Do you really want to delete this task?')) return;

    router.delete(`/tasks/${task.id}`, {
        preserveScroll: true,
        preserveState: true,
        only: ['tasks', 'filters', 'stats'],
        onSuccess: () => {
            if (activeTask.value?.id === task.id) {
                activeTask.value = null;
                editDialogOpen.value = false;
                detailDialogOpen.value = false;
            }
        },
    });
}

function statusLabel(task: Task): string {
    return task.status ? 'Completed' : 'Pending';
}

const hasTasks = computed(() => (sortedTasks.value?.length ?? 0) > 0);

function clearFilters() {
    filterForm.status = 'all';
    filterForm.priority = null;
    filterForm.due_date = '';
    filterForm.search = '';
    applyFilters();
}
</script>

<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-content flex flex-col gap-6 p-4 lg:p-6">
            <div
                class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-[0.08em] text-neutral-500 uppercase"
                    >
                        Organize your day
                    </p>
                    <h1
                        class="text-3xl leading-tight font-semibold text-foreground"
                    >
                        Task List
                    </h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        Create, track, and complete activities with instant
                        feedback.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        variant="outline"
                        class="gap-2"
                        @click="clearFilters"
                    >
                        <Filter class="size-4" />
                        Clear filters
                    </Button>
                    <Dialog v-model:open="createDialogOpen">
                        <DialogTrigger as-child>
                            <Button class="gap-2">
                                <Plus class="size-4" />
                                New task
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="modal-silver-shadow">
                            <DialogHeader>
                                <DialogTitle>New task</DialogTitle>
                                <DialogDescription>
                                    Set title, priority, and due date to better
                                    track your tasks.
                                </DialogDescription>
                            </DialogHeader>
                            <form
                                class="space-y-4"
                                @submit.prevent="submitCreate"
                            >
                                <div class="space-y-2">
                                    <label class="text-sm font-medium"
                                        >Title *</label
                                    >
                                    <Input
                                        v-model="createForm.title"
                                        placeholder="E.g.: Finish presentation"
                                    />
                                    <p
                                        v-if="createForm.errors.title"
                                        class="text-xs text-destructive"
                                    >
                                        {{ createForm.errors.title }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium"
                                        >Description</label
                                    >
                                    <textarea
                                        v-model="createForm.description"
                                        rows="3"
                                        class="w-full rounded-md border border-border bg-background p-3 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                                        placeholder="Quick details or checklist."
                                    ></textarea>
                                    <p
                                        v-if="createForm.errors.description"
                                        class="text-xs text-destructive"
                                    >
                                        {{ createForm.errors.description }}
                                    </p>
                                </div>
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium"
                                            >Due date</label
                                        >
                                        <Input
                                            v-model="createForm.due_date"
                                            type="date"
                                        />
                                        <p
                                            v-if="createForm.errors.due_date"
                                            class="text-xs text-destructive"
                                        >
                                            {{ createForm.errors.due_date }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium"
                                            >Priority *</label
                                        >
                                        <select
                                            v-model="createForm.priority"
                                            class="w-full rounded-md border border-border bg-background p-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                                        >
                                            <option
                                                v-for="priority in priorities"
                                                :key="priority"
                                                :value="priority"
                                            >
                                                {{ priorityLabels[priority] }}
                                            </option>
                                        </select>
                                        <p
                                            v-if="createForm.errors.priority"
                                            class="text-xs text-destructive"
                                        >
                                            {{ createForm.errors.priority }}
                                        </p>
                                    </div>
                                </div>
                                <DialogFooter class="gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="createDialogOpen = false"
                                        >Cancel</Button
                                    >
                                    <Button
                                        type="submit"
                                        :disabled="createForm.processing"
                                        >Save task</Button
                                    >
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="dashboard-card rounded-xl border border-border/70 bg-card p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-10 place-content-center rounded-lg bg-primary/10 text-primary"
                        >
                            <CalendarClock class="size-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs tracking-[0.08em] text-neutral-500 uppercase"
                            >
                                Total
                            </p>
                            <p class="text-2xl font-semibold">
                                {{ stats.total }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="dashboard-card rounded-xl border border-border/70 bg-card p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-10 place-content-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-200"
                        >
                            <CheckCircle2 class="size-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs tracking-[0.08em] text-neutral-500 uppercase"
                            >
                                Completed
                            </p>
                            <p class="text-2xl font-semibold">
                                {{ stats.completed }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="dashboard-card rounded-xl border border-border/70 bg-card p-4 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid size-10 place-content-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-200"
                        >
                            <Clock4 class="size-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs tracking-[0.08em] text-neutral-500 uppercase"
                            >
                                Pending
                            </p>
                            <p class="text-2xl font-semibold">
                                {{ stats.pending }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="dashboard-card rounded-xl border border-border/70 bg-card p-4 shadow-sm"
            >
                <div class="grid gap-3 md:grid-cols-4 md:items-end">
                    <div class="space-y-1">
                        <label
                            class="text-xs tracking-wide text-neutral-500 uppercase"
                            >Status</label
                        >
                        <select
                            v-model="filterForm.status"
                            class="w-full rounded-md border border-border bg-background p-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-xs tracking-wide text-neutral-500 uppercase"
                            >Priority</label
                        >
                        <select
                            v-model="filterForm.priority"
                            class="w-full rounded-md border border-border bg-background p-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                        >
                            <option :value="null">All</option>
                            <option
                                v-for="priority in priorities"
                                :key="priority"
                                :value="priority"
                            >
                                {{ priorityLabels[priority] }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-xs tracking-wide text-neutral-500 uppercase"
                            >Due date</label
                        >
                        <Input v-model="filterForm.due_date" type="date" />
                    </div>
                    <div class="space-y-1">
                        <label
                            class="text-xs tracking-wide text-neutral-500 uppercase"
                            >Search</label
                        >
                        <Input
                            v-model="filterForm.search"
                            placeholder="Search by title or description"
                        />
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-if="!hasTasks"
                    class="dashboard-card col-span-full flex flex-col items-center justify-center rounded-xl border border-dashed border-border/70 bg-card p-8 text-center"
                >
                    <BadgeCheck class="mb-3 size-10 text-neutral-400" />
                    <p class="text-lg font-semibold">No tasks found</p>
                    <p class="text-sm text-neutral-500">
                        Create new tasks or adjust the filters to see items.
                    </p>
                </div>

                <div
                    v-for="task in sortedTasks"
                    :key="task.id"
                    class="group dashboard-card flex flex-col gap-3 rounded-xl border border-border/70 bg-card p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg leading-tight font-semibold">
                                    {{ task.title }}
                                </h3>
                                <Badge
                                    :class="priorityColors[task.priority]"
                                    variant="outline"
                                >
                                    {{ priorityLabels[task.priority] }}
                                </Badge>
                            </div>
                            <p class="line-clamp-2 text-sm text-neutral-500">
                                {{ task.description || 'No description' }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="rounded-full border border-border p-2 text-neutral-500 transition hover:border-primary/40 hover:text-primary"
                                @click="toggleStatus(task)"
                            >
                                <CheckCircle2
                                    class="size-5"
                                    :class="
                                        task.status ? 'text-emerald-500' : ''
                                    "
                                />
                            </button>
                            <button
                                class="rounded-full border border-border p-2 text-neutral-500 transition hover:border-primary/40 hover:text-primary"
                                @click="openEdit(task)"
                            >
                                <Pencil class="size-5" />
                            </button>
                            <button
                                class="rounded-full border border-destructive/40 p-2 text-destructive transition hover:border-destructive hover:bg-destructive/10"
                                @click="deleteTask(task)"
                            >
                                <Trash2 class="size-5" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-2 text-sm text-neutral-500"
                    >
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1 text-xs font-medium"
                        >
                            <Clock4 class="size-4" />
                            {{ statusLabel(task) }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1 text-xs font-medium"
                        >
                            <CalendarClock class="size-4" />
                            {{ formatDate(task.due_date) }}
                        </span>
                        <span
                            v-if="task.overdue"
                            class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2 py-1 text-xs font-semibold text-destructive"
                        >
                            <Info class="size-4" /> Overdue
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div
                            class="flex items-center gap-2 text-xs tracking-[0.08em] text-neutral-500 uppercase"
                        >
                            <span>Updated</span>
                        </div>
                        <button
                            class="text-sm font-semibold text-primary hover:underline"
                            @click="openDetails(task)"
                        >
                            View details
                        </button>
                    </div>
                </div>
            </div>

            <Dialog v-model:open="editDialogOpen">
                <DialogContent v-if="activeTask" class="modal-silver-shadow">
                    <DialogHeader>
                        <DialogTitle>Edit task</DialogTitle>
                        <DialogDescription>
                            Update information or mark as completed.
                        </DialogDescription>
                    </DialogHeader>
                    <form class="space-y-4" @submit.prevent="submitEdit">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Title *</label>
                            <Input v-model="editForm.title" />
                            <p
                                v-if="editForm.errors.title"
                                class="text-xs text-destructive"
                            >
                                {{ editForm.errors.title }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium"
                                >Description</label
                            >
                            <textarea
                                v-model="editForm.description"
                                rows="3"
                                class="w-full rounded-md border border-border bg-background p-3 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                            ></textarea>
                            <p
                                v-if="editForm.errors.description"
                                class="text-xs text-destructive"
                            >
                                {{ editForm.errors.description }}
                            </p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium"
                                    >Due date</label
                                >
                                <Input
                                    v-model="editForm.due_date"
                                    type="date"
                                />
                                <p
                                    v-if="editForm.errors.due_date"
                                    class="text-xs text-destructive"
                                >
                                    {{ editForm.errors.due_date }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium"
                                    >Priority *</label
                                >
                                <select
                                    v-model="editForm.priority"
                                    class="w-full rounded-md border border-border bg-background p-2.5 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                                >
                                    <option
                                        v-for="priority in priorities"
                                        :key="priority"
                                        :value="priority"
                                    >
                                        {{ priorityLabels[priority] }}
                                    </option>
                                </select>
                                <p
                                    v-if="editForm.errors.priority"
                                    class="text-xs text-destructive"
                                >
                                    {{ editForm.errors.priority }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-2 rounded-lg border border-border/70 bg-muted/30 p-3"
                        >
                            <input
                                id="edit-status"
                                v-model="editForm.status"
                                type="checkbox"
                                class="size-4 rounded border-border text-primary focus:ring-2 focus:ring-ring/40"
                            />
                            <label for="edit-status" class="text-sm font-medium"
                                >Mark as completed</label
                            >
                        </div>
                        <DialogFooter class="gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                @click="editDialogOpen = false"
                                >Cancel</Button
                            >
                            <Button
                                type="submit"
                                :disabled="editForm.processing"
                                >Save changes</Button
                            >
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="detailDialogOpen">
                <DialogContent v-if="activeTask" class="modal-silver-shadow">
                    <DialogHeader>
                        <DialogTitle>{{ activeTask.title }}</DialogTitle>
                        <DialogDescription
                            class="flex flex-wrap items-center gap-2"
                        >
                            <Badge
                                :class="priorityColors[activeTask.priority]"
                                variant="outline"
                            >
                                {{ priorityLabels[activeTask.priority] }}
                            </Badge>
                            <Badge variant="secondary">{{
                                statusLabel(activeTask)
                            }}</Badge>
                            <Badge variant="outline"
                                >Due:
                                {{ formatDate(activeTask.due_date) }}</Badge
                            >
                        </DialogDescription>
                    </DialogHeader>
                    <div
                        class="space-y-3 text-sm text-neutral-600 dark:text-neutral-300"
                    >
                        <p class="leading-relaxed">
                            {{
                                activeTask.description ||
                                'No description for this task.'
                            }}
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <Badge variant="outline"
                                >ID #{{ activeTask.id }}</Badge
                            >
                            <Badge
                                v-if="activeTask.overdue"
                                variant="destructive"
                                >Overdue</Badge
                            >
                        </div>
                    </div>
                    <DialogFooter class="gap-2">
                        <Button
                            variant="outline"
                            @click="detailDialogOpen = false"
                            >Close</Button
                        >
                        <Button
                            variant="secondary"
                            class="gap-2"
                            @click="
                                openEdit(activeTask);
                                detailDialogOpen = false;
                            "
                        >
                            <Pencil class="size-4" /> Edit
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
.modal-silver-shadow {
    box-shadow:
        0 0 0 2px #c0c0c0,
        0 8px 32px 0 #c0c0c0,
        0 2px 8px 0 rgba(0, 0, 0, 0.04);
    border-color: #c0c0c0 !important;
    border-width: 1px !important;
    transition:
        box-shadow 0.2s,
        border-color 0.2s;
}
.modal-silver-shadow:hover {
    box-shadow:
        0 0 0 2.5px #b0b0b0,
        0 12px 40px 0 #b0b0b0,
        0 4px 16px 0 rgba(0, 0, 0, 0.08);
    border-color: #b0b0b0 !important;
}
.dashboard-card {
    border-width: 0.5px !important;
    border-color: rgba(220, 38, 38, 0.7) !important;
    box-shadow:
        0 0 0 0.5px rgba(220, 38, 38, 0.7),
        /* ultra-thin red outline */ 0 2px 12px 0 rgba(220, 38, 38, 0.12),
        /* red drop shadow */ 0 2px 8px 0 rgba(0, 0, 0, 0.04); /* subtle base shadow */
    transition:
        box-shadow 0.2s,
        border-color 0.2s;
}
.dashboard-card:hover {
    box-shadow:
        0 0 0 1px rgba(220, 38, 38, 0.9),
        0 4px 20px 0 rgba(220, 38, 38, 0.18),
        0 4px 16px 0 rgba(0, 0, 0, 0.08);
    border-color: rgba(220, 38, 38, 0.9) !important;
}
</style>
