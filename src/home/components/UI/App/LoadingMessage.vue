<template>
  <div
    class="mt-5 flex items-center justify-center gap-4 font-medium text-purple"
  >
    <LoadingSpin />
    Loading Patrol Planner (this may take a few seconds)
  </div>
</template>
<script setup lang="ts">
import LoadingSpin from "@/patrolplanner/components/UI/LoadingSpin.vue";
</script>
