<script setup lang="ts">
import { CheckSquare } from 'lucide-vue-next';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Tasks',
        href: '/tasks',
        icon: CheckSquare,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <a
                            href="https://www.inovcorp.com/pt/home"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex cursor-pointer items-center gap-0.5 rounded-md px-2 py-1 tracking-tighter transition-all duration-200 select-none hover:scale-105 hover:bg-primary/10 hover:shadow-lg"
                        >
                            <span class="text-base font-bold">
                                Ino<span
                                    class="align-middle text-2xl font-extrabold text-primary"
                                    style="
                                        text-shadow:
                                            1px 1px 6px #b0b6be,
                                            0 0 2px #e0e0e0;
                                        letter-spacing: 0.04em;
                                    "
                                    >V</span
                                >Corp
                            </span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
