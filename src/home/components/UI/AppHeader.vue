<script setup lang="ts">
import { defineProps } from "vue";
import LoadingSpin from "@/patrolplanner/components/UI/LoadingSpin.vue";

const props = defineProps({
  auth: {
    type: Object,
    required: true,
  },
});

const apiBase = import.meta.env.VITE_BASE_URL || "/";
</script>

<template>
  <header class="bg-purple p-3">
    <div class="max-w-6xl mx-auto flex items-center gap-5">
      <img
        src="/src/patrolplanner/assets/fleur-de-lis.png"
        alt="Fleur de Lis"
        class="h-10"
      />
      <div>
        <p class="font-semibold text-white">OSM Integration Tools</p>
        <p class="text-sm text-white">City of Newcastle Scouts</p>
      </div>
    </div>
  </header>
</template>
