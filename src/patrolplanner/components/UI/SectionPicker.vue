<script setup lang="ts">
import { defineProps } from "vue";
import * as types from "@/patrolplanner/types";

const props = defineProps<{
  sections: object;
}>();

const emit = defineEmits(["changeSection"]);

const change = (data: object) => {
  emit("changeSection", data);
};
</script>

<template>
  <h2 class="font-medium text-purple text-xl">Select a Section to Begin</h2>

  <div class="flex mt-2 flex-col gap-4">
    <div v-for="(sections, group) in props.sections" :key="group">
      <h3 class="text font-medium">
        {{ group }}
      </h3>
      <div class="flex gap-2 mt-2 ml-2">
        <a
          @click="change({ group, section })"
          href="javascript:void(0)"
          v-for="section in sections"
          :key="section"
          :class="// @ts-ignore
          `py-1 text-sm rounded-md px-4 text-white bg-${section.colour} hover:opacity-75 transition-opacity`"
        >
          <h4 class="font-medium">
            {{
              // @ts-ignore
              section.name
            }}
          </h4>
        </a>
      </div>
    </div>
  </div>
</template>
