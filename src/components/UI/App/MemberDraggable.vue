<script setup lang="ts">
import { defineProps, defineEmits, defineExpose, ref } from "vue";
import type { Member } from "@/types";
import { imageReplace } from "@/util";
import { ChevronUpIcon, ChevronDoubleUpIcon } from "@heroicons/vue/20/solid";

const props = defineProps<{
  member: Member;
  patrol: number;
}>();

const member = ref(props.member);
</script>

<template>
  <div
    class="flex items-center gap-2 px-3 py-1 border-b last:border-b-0 first:border-t"
    @click="$emit('click')"
  >
    <img
      @error="imageReplace($event, member)"
      :src="member.photo_url"
      :alt="member.name"
      class="h-8 w-8 rounded-md"
    />
    <div class="flex-1">
      <p class="text-sm flex-1 font-medium">
        {{ member.forename }} {{ member.surname }}
      </p>
      <p
        v-if="member.patrolid >= 0"
        class="text-[0.6rem] -mt-0.5 text-slate-500"
      >
        {{ member.age }}
      </p>
    </div>
    <div v-if="patrol > -2">
      <ChevronUpIcon
        class="h-4 text-purple"
        v-if="member.rank === 1"
      ></ChevronUpIcon>
      <ChevronDoubleUpIcon
        class="h-4 text-purple"
        v-else-if="member.rank === 2"
      ></ChevronDoubleUpIcon>
    </div>
    <p v-if="patrol > -2" class="text-xs uppercase font-bold text-purple">
      {{ member.rank_abbr }}
    </p>
  </div>
</template>
