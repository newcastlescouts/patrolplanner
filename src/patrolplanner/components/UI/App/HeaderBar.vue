<script setup lang="ts">
import { defineProps } from "vue";
import type * as types from "@/patrolplanner/types";

const props = defineProps<{
  section: types.Section;
  group: string;
  term: string;
}>();

// eslint-disable-next-line vue/no-setup-props-destructure
const { section, group, term } = props;
</script>

<template>
  <div class="flex gap-3 items-center">
    <div>
      <h2 class="text-purple uppercase font-bold">
        {{ group }}
      </h2>
      <h1 class="font-medium text-3xl">{{ section.name }} - {{ term }}</h1>
    </div>

    <div class="flex-1"></div>

    <a
      href="javascript:void(0)"
      @click="$emit('changeSection', {})"
      class="bg-white py-2 px-5 hover:bg-slate-200 transition-colors rounded-md shadow-sm text-sm font-medium text-purple"
    >
      Back Home
    </a>

    <a
      href="javascript:void(0)"
      @click="$emit('save')"
      class="bg-purple py-2 px-5 hover:opacity-75 transition-opacity rounded-md shadow-sm text-sm font-medium text-white"
    >
      Save Changes
    </a>
  </div>
</template>
