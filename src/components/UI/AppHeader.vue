<script setup lang="ts">
import { defineProps } from "vue";
import LoadingSpin from "@/components/UI/LoadingSpin.vue";

const props = defineProps({
  auth: {
    type: Object,
    required: true,
  },
});

const apiBase = import.meta.env.VITE_BASE_URL || "/";

const connectOsm = () => {
  window.location.href = apiBase + "api/api.php?action=authenticate";
};

const disconnect = () => {
  if (
    confirm("Are you sure you want to disconnect from Online Scout Manager?")
  ) {
    localStorage.removeItem("session");
    window.location.href = apiBase + "api/callback.php?logout";
  }
};
</script>

<template>
  <header class="bg-purple p-3">
    <div class="max-w-6xl mx-auto flex items-center gap-5">
      <img src="/src/assets/fleur-de-lis.png" alt="Fleur de Lis" class="h-10" />
      <div>
        <p class="font-semibold text-white">Patrol Planner</p>
        <p class="text-sm text-white">OSM Integration Tool</p>
      </div>
      <div class="flex-1"></div>
      <div
        class="bg-white flex justify-center items-center w-36 text-center py-1 px-4 rounded text-purple font-semibold text-sm border shadow opacity-75"
        v-if="props.auth.loading"
      >
        <LoadingSpin />
      </div>
      <a
        href="javascript:void(0)"
        class="bg-white w-36 text-center py-1 px-4 rounded text-purple font-semibold text-sm border shadow hover:bg-slate-200 transition-all"
        v-else-if="!props.auth.authenticated"
        @click="connectOsm"
      >
        Connect to OSM
      </a>
      <a
        href="javascript:void(0)"
        class="bg-white w-36 text-center py-1 px-4 rounded text-purple font-semibold text-sm border shadow hover:bg-slate-200 transition-all"
        v-else-if="props.auth.authenticated"
        @click="disconnect"
      >
        Hi, {{ props.auth.name }}!
      </a>
    </div>
  </header>
</template>
