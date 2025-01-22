<script setup lang="ts">
// @ts-nocheck (breaks a bunch of stuff that actually works)
import AppHeader from "@/components/UI/AppHeader.vue";
import AppFooter from "@/components/UI/AppFooter.vue";
import { ref, onMounted } from "vue";
import apiFetch from "./apiFetch";
import NonAuth from "@/components/MainScreen/NonAuth.vue";
import SectionPicker from "@/components/UI/SectionPicker.vue";
import PatrolPlanner from "@/components/UI/PatrolPlanner.vue";
import type * as types from "@/types";

const auth = ref({
  loading: true,
  authenticated: false,
  name: "",
  roles: {},
  terms: {},
});

const dummySection: types.SectionPayload = {
  group: "",
  section: {
    name: "",
    sectionid: 0,
    groupid: 0,
    member_permission: 0,
    colour: "",
    section: "",
  },
};

const currentSection = ref<object>({});

const base = import.meta.env.VITE_BASE_URL || "/";

onMounted(async () => {
  const res = await apiFetch("api/api.php?action=status", { baseURL: base });

  auth.value.loading = false;

  if (!res.authenticated) {
    auth.value.authenticated = false;
  } else {
    auth.value.authenticated = true;
    auth.value.name = res.firstname;
    auth.value.roles = res.roles;
    auth.value.terms = res.terms;
  }
});

const changeSection = (section: object) => {
  currentSection.value = section;
};
</script>

<template>
  <AppHeader :auth="auth" />

  <AppFooter />

  <main
    class="flex-1 bg-slate-100 h-full flex flex-col justify-center items-center"
    v-if="auth.loading"
  >
    <h1 class="text-4xl">Application Loading</h1>
    <h2 class="text-xl">This may take a few seconds.</h2>
  </main>
  <main
    class="flex-1 bg-slate-100 flex flex-col h-full p-5"
    v-else-if="!auth.authenticated"
  >
    <NonAuth />
  </main>
  <main
    class="flex-1 bg-slate-100 print:bg-transparent h-full flex flex-col"
    v-else
  >
    <!-- ensure tailwind compiles these -->
    <div class="hidden bg-blue bg-green bg-navy"></div>

    <div class="max-w-screen-xl w-full mx-auto p-5 flex flex-1 flex-col">
      <SectionPicker
        @changeSection="changeSection"
        :sections="auth.roles"
        v-if="Object.keys(currentSection).length === 0"
      />
      <PatrolPlanner
        @changeSection="changeSection"
        v-else
        :data="
          Object.keys(currentSection).length === 0
            ? dummySection
            : currentSection
        "
        :terms="auth.terms"
      />
    </div>
  </main>
</template>
