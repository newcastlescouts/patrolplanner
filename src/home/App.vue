<template>
  <AppHeader />

  <div class="p-5 max-w-6xl mx-auto w-full">
    <h1 class="text-xl font-semibold">
      Tools for integrating with Online Scout Manager
    </h1>
    <p>
      These set of open source tools allow leaders to integrate with Online
      Scout Manager's set of tools to provide additional functionality,
      reporting and planning.
    </p>
    <p>
      These tools may be used by any section in the UK, however, support is only
      provided to the City of Newcastle District. If you are from another
      district,
      <a
        class="text-navy"
        href="javascript:void(0)"
        @click="showDisclaimer = true"
      >
        our warranty disclaimer applies
      </a>
    </p>

    <div class="bg-yellow p-5" v-if="showDisclaimer">
      <p class="font-bold text-lg">
        Warranty Disclaimer
        <a
          href="javascript:void(0)"
          class="text-blue"
          @click="showDisclaimer = false"
          >[close]</a
        >
      </p>
      <p>
        ALL SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
        OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
        MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
        IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
        CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
        TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
        SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
      </p>
      <p>
        This software is provided as a free service by the City of Newcastle
        upon Tyne District Scouts. It is not an official product of The Scout
        Association. It is provided as is and without warranty. This software
        has no affiliations implied or otherwise with The Scout Association or
        Online Scout Manager.
      </p>
    </div>

    <p class="text-center mt-12 font-semibold text-slate-600">
      Select a tool to get started.
    </p>
    <div class="flex gap-5 mt-4 justify-center">
      <a
        v-for="tool in tools"
        :key="tool.title"
        :href="tool.link"
        class="p-5 text-white bg-blue rounded w-72 hover:bg-navy transition-colors"
      >
        <p class="font-semibold text-lg text-center">{{ tool.title }}</p>
        <p>
          {{ tool.description }}
        </p>
      </a>
    </div>
  </div>
</template>
<script setup lang="ts">
import AppHeader from "@/home/components/UI/AppHeader.vue";
import { ref } from "vue";

const showDisclaimer = ref(false);

const tools = ref([
  {
    title: "Patrol Planner",
    description:
      "Visually plan patrols, sixes and lodges with a drag and drop interface.",
    link: "/patrolplanner.html",
  },
  {
    title: "Event Overview",
    description:
      "View an event overview to see who is attending an event and outline medical and dietary requirements.",
    link: "/eventoverview.html",
  },
]);
</script>
