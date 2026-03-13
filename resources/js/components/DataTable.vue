<script setup lang="ts" generic="TData">
import { Link } from '@inertiajs/vue3';
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
    type ColumnDef,
} from '@tanstack/vue-table';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';

export type PaginationMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    columns: ColumnDef<TData, unknown>[];
    data: TData[];
    pagination?: PaginationMeta;
    emptyMessage?: string;
}>();

const table = useVueTable({
    get data() { return props.data; },
    get columns() { return props.columns; },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    get pageCount() { return props.pagination?.last_page ?? -1; },
});
</script>

<template>
    <div>
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="hover:bg-muted/50"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center text-muted-foreground">
                                <slot name="empty">
                                    {{ emptyMessage ?? 'Tidak ada data.' }}
                                </slot>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Server-side pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-muted-foreground">
            <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }} ({{ pagination.total }} data)</span>
            <div class="flex gap-1">
                <template v-for="link in pagination.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded border px-3 py-1 text-sm"
                        :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                        v-html="link.label"
                        preserve-state
                    />
                    <span
                        v-else
                        class="rounded border px-3 py-1 text-sm opacity-50"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
