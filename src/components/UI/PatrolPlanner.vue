<script setup lang="ts">
import { onMounted, ref } from "vue";
import apiFetch from "@/apiFetch";
import HeaderBar from "@/components/UI/App/HeaderBar.vue";
import * as types from "@/types";
// noinspection ES6UnusedImports
import draggable from "vuedraggable";
import { rankAbbreviations, rankLabels } from "@/rankTables";
import { imageReplace } from "@/util";
import MemberDraggable from "@/components/UI/App/MemberDraggable.vue";
import LoadingMessage from "@/components/UI/App/LoadingMessage.vue";
import LoadingSpin from "@/components/UI/LoadingSpin.vue";

const props = defineProps<{
  data: types.SectionPayload;
  terms: any;
}>();

const term = ref<any>(null);

const loadingData = ref(true);

const activeMember = ref<any>(null);

const patrols = ref<any>({});
const dragging = ref<boolean>(false);

onMounted(async () => {
  const sectionid = props.data.section.sectionid;
  const terms = props.terms[sectionid];
  term.value = terms[terms.length - 1];

  const termid = term.value!!.termid;
  const section = props.data.section.section;

  const query = `&section=${section}&sectionid=${sectionid}&termid=${termid}`;
  const res = await apiFetch("/api/api.php?action=members" + query);

  for (const i in res.items) {
    const scout = res.items[i];
    const split_patrol = scout.patrol.split(" ");
    const patrolid = scout.patrolid;
    const patrol = split_patrol[0];
    const rank = scout.patrol_role_level_label;
    const scoutid = scout.scoutid;
    const photo_guid = scout.photo_guid;

    // get first 4 chars of scout id, then pad with 0s to 7 chars
    const photo_start = scoutid.toString().slice(0, 4) + "000";
    let photo_url = `https://www.onlinescoutmanager.co.uk/sites/onlinescoutmanager.co.uk/public/member_photos/${photo_start}/${scoutid}/${photo_guid}/250x250_0.jpg`;

    if (photo_guid === null) {
      photo_url = `https://api.dicebear.com/5.x/fun-emoji/svg?seed=${scoutid}`;
    }

    let rank_number = 0;
    if (rank === "Senior Sixer" || rank === "Senior Patrol Leader") {
      rank_number = 3;
    } else if (rank === "Sixer" || rank === "Patrol Leader") {
      rank_number = 2;
    } else if (rank === "Assistant Patrol Leader" || rank === "Seconder") {
      rank_number = 1;
    }

    let rank_abbrev = "";
    if (split_patrol.length > 1) {
      rank_abbrev = split_patrol[1];
    }

    const member: types.Member = {
      name: scout.name,
      forename: scout.firstname,
      surname: scout.lastname,
      patrolid: patrolid,
      scoutid: scoutid,
      active: scout.active,
      role_level_label: rank,
      patrol_label: patrol,
      photo_url: photo_url,
      rank: rank_number,
      rank_abbr: rank_abbrev,
      age: scout.age,
    };

    if (patrols.value[patrolid] === undefined) {
      patrols.value[patrolid] = {
        name: patrol,
        patrolid: patrolid,
        members: [member],
      };
    } else {
      patrols.value[patrolid].members.push(member);
    }
  }

  patrols.value = Object.fromEntries(
    Object.entries(patrols.value).map(([key, value]) => {
      // @ts-ignore
      value.members = value.members.sort((a: types.Member, b: types.Member) => {
        return b.rank - a.rank;
      });
      return [key, value];
    })
  );

  loadingData.value = false;
});

const getRank = (level: number): string => {
  return rankLabels[props.data.section.section][level];
};

const getRankAbbr = (level: number): string => {
  return rankAbbreviations[props.data.section.section][level];
};

interface ChangedStateLiteral {
  [key: number]: number;
}
const changedPatrol: ChangedStateLiteral = {};
const changedRank: ChangedStateLiteral = {};

const promote = (member: types.Member) => {
  if (member.rank < 3) {
    member.rank += 1;
    member.role_level_label = getRank(member.rank);
    member.rank_abbr = getRankAbbr(member.rank);

    changedRank[member.scoutid] = member.rank;
  }
};

const demote = (member: types.Member) => {
  if (member.rank > 0) {
    member.rank -= 1;
    member.role_level_label = getRank(member.rank);
    member.rank_abbr = getRankAbbr(member.rank);

    changedRank[member.scoutid] = member.rank;
  }
};

const saveQueue = ref<types.SaveQueueEntry[]>([]);
const queueComplete = ref(false);

const saveChanges = async () => {
  // Populate the save queue
  const _patrols = Object.assign({}, patrols.value);
  for (const id in _patrols) {
    const patrol = _patrols[id];
    console.log(patrol);
    for (const idx in patrol.members) {
      const member = patrol.members[idx];
      if (changedPatrol[member.scoutid] !== undefined) {
        saveQueue.value.push({
          member: member,
          value: changedPatrol[member.scoutid],
          state: types.SaveQueueState.WAITING,
          type: types.SaveQueueType.PATROL_CHANGE,
        });
      }

      if (changedRank[member.scoutid] !== undefined) {
        console.log(
          "Add Operation",
          saveQueue.value.push({
            member: member,
            value: changedRank[member.scoutid],
            state: types.SaveQueueState.WAITING,
            type: types.SaveQueueType.RANK_CHANGE,
          })
        );
      }
    }
  }

  console.log("Save Queue", saveQueue.value);

  for (const entry of saveQueue.value) {
    entry.state = types.SaveQueueState.SAVING;
    const action =
      entry.type === types.SaveQueueType.PATROL_CHANGE
        ? "patrolchange"
        : "rankchange";

    const data = {
      scoutid: entry.member.scoutid.toString(),
      patrolid: entry.value,
      rank: entry.value,
      sectionid: props.data.section.sectionid.toString(),
    };

    try {
      await apiFetch("/api/api.php?action=" + action, {
        method: "POST",
        body: data,
      });

      entry.state = types.SaveQueueState.SAVED;
    } catch (e) {
      console.log(e);
      entry.state = types.SaveQueueState.ERROR;
    }

    // Ensure OSM doesn't rate limit us
    await new Promise((resolve) => setTimeout(resolve, 1000));
  }

  queueComplete.value = true;
};

const clearQueue = () => {
  saveQueue.value = [];
  queueComplete.value = false;
};

const endDrag = (value: any, patrolid: number) => {
  if (Object.keys(value).includes("added")) {
    console.log("Adding user to save queue", value.added.element);
    console.log(patrolid);
    changedPatrol[value.added.element.scoutid] = patrolid;
  }
};

const averageAge = (patrolid: number) => {
  let total = 0;

  for (const member of patrols.value[patrolid].members) {
    const age = member.age.split(" / ");
    total += parseInt(age[0]) + parseInt(age[1]) / 12;
  }

  return total / patrols.value[patrolid].members.length;
};

const formatAge = (rawAge: string | number) => {
  if (typeof rawAge === "string") {
    rawAge = parseFloat(rawAge);
  }

  const age = rawAge as number;

  const years = Math.floor(age);
  const months = Math.round((age - years) * 12);

  return `${years} / ${months}`;
};
</script>

<template>
  <div v-if="term !== null" class="flex flex-col gap-5 flex-1 h-full relative">
    <HeaderBar
      @changeSection="$emit('changeSection', {})"
      @save="saveChanges"
      :term="term.name"
      :group="data.group"
      :section="data.section"
    />

    <div
      class="absolute bg-black/10 -top-4 -left-16 bottom-36 -right-16"
      v-if="saveQueue.length"
    >
      <div class="flex justify-center items-center h-full">
        <div class="bg-white max-w-6xl w-full border rounded-xl">
          <div
            class="h-12 bg-purple w-full rounded-t-xl flex gap-5 items-center justify-center text-white font-medium"
          >
            <p>Applying Changes</p>
            <a
              @click="clearQueue"
              href="javascript:void(0)"
              v-if="queueComplete"
              class="bg-white text-purple text-sm rounded px-2 py-1"
            >
              Complete. Click to close.
            </a>
          </div>
          <div class="p-2 bg-slate-100 rounded-b-xl">
            <p class="text-center mb-2.5 text-sm text-gray-600">
              Changes are being sent to OSM. This could take several minutes
              depending on the number of members in your section.
            </p>

            <div class="flex gap-4 flex-wrap">
              <div
                v-for="(entry, i) in saveQueue"
                :key="i"
                class="rounded-md flex bg-white shadow-sm w-64 justify-items-stretch"
              >
                <img
                  :src="entry.member.photo_url"
                  :alt="entry.member.forename"
                  class="h-12 w-12 rounded-l-md"
                  @error="imageReplace($event, entry.member)"
                />
                <div class="p-2 text-xs font-medium">
                  <p>
                    {{ entry.member.forename }} {{ entry.member.surname }} ({{
                      entry.type
                    }})
                  </p>
                  <div class="text-xs">
                    <p v-if="entry.state === types.SaveQueueState.WAITING">
                      Not Started
                    </p>
                    <p
                      v-else-if="entry.state === types.SaveQueueState.SAVING"
                      class="flex gap-2 items-center"
                    >
                      <LoadingSpin class="h-3 w-3"></LoadingSpin>
                      <span class="animate-pulse text-purple font-bold"
                        >Saving</span
                      >
                    </p>
                    <p
                      v-else-if="entry.state === types.SaveQueueState.SAVED"
                      class="flex gap-2 items-center"
                    >
                      <span class="text-green">Saved</span>
                    </p>
                    <p
                      v-else-if="entry.state === types.SaveQueueState.ERROR"
                      class="flex gap-2 items-center"
                    >
                      <span class="text-red">Errored</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="activeMember">
      <div class="bg-white rounded-xl w-96 mx-auto mt-6 shadow-sm flex">
        <img
          :src="activeMember.member.photo_url"
          @error="imageReplace($event, activeMember.member)"
          class="h-32 w-32 rounded-l-xl"
          alt="Member Image"
        />
        <div class="p-3.5 flex flex-col">
          <p class="text-purple font-medium">
            {{ activeMember.member.forename }} {{ activeMember.member.surname }}
          </p>
          <p class="text-sm">
            <span v-if="activeMember.patrol.patrolid === -2"> Leaders </span>
            <span v-else-if="activeMember.patrol.patrolid === -3">
              Young Leaders
            </span>
            <span v-else-if="activeMember.patrol.patrolid === 0">
              Not Assigned
            </span>
            <span v-else>
              {{ activeMember.patrol.name }}
              {{ activeMember.member.role_level_label }}
            </span>
          </p>
          <div class="flex-1"></div>
          <div
            class="text-sm font-medium text-red"
            v-if="activeMember.member.rank < 3"
          >
            <a href="javascript:void(0)" @click="promote(activeMember.member)">
              Promote to {{ getRank(activeMember.member.rank + 1) }}
            </a>
          </div>
          <div
            class="text-sm font-medium text-red"
            v-if="activeMember.member.rank > 0"
          >
            <a href="javascript:void(0)" @click="demote(activeMember.member)">
              Demote to {{ getRank(activeMember.member.rank - 1) }}
            </a>
          </div>
        </div>
      </div>
    </div>

    <LoadingMessage v-if="loadingData" />

    <div
      v-else
      class="flex gap-2 print:gap-0 flex-wrap mt-12 print:mt-0 print:pt-1.5 print:border justify-center print:justify-start"
    >
      <!--suppress HtmlUnknownTag -->
      <draggable
        v-for="patrol in patrols"
        :key="patrol.patrolid"
        :list="patrol.members"
        item-key="scoutid"
        class="bg-white rounded-xl print:rounded-none w-72 print:w-1/3 cursor-pointer flex flex-col print:border-x print:border-b"
        group="members"
        @start="dragging = true"
        @change="(e) => endDrag(e, patrol.patrolid)"
        :disabled="false"
      >
        <template #header>
          <div
            class="px-5 py-2.5 print:px-1.5 print:py-0 border-b flex justify-between items-center"
          >
            <h2 class="text-purple font-bold text-lg print:text-base">
              <span v-if="patrol.patrolid === -2"> Leaders </span>
              <span v-else-if="patrol.patrolid === -3"> Young Leaders </span>
              <span v-else-if="patrol.patrolid === 0"> Not Assigned </span>
              <span v-else> {{ patrol.name }} </span>
            </h2>

            <div class="text-right text-slate-500">
              <p class="text-xs">
                {{ patrol.members.length }}

                <span class="hidden print:inline"> members </span>
              </p>
            </div>

            <span class="hidden print:inline text-xs">
              Average {{ formatAge(averageAge(patrol.patrolid).toFixed(1)) }}
            </span>
          </div>
        </template>
        <template #item="{ element }">
          <MemberDraggable
            @click="activeMember = { patrol, member: element }"
            :patrol="patrol.patrolid"
            :member="element"
          />
        </template>

        <template #footer>
          <div class="flex-1 py-1" />
          <div
            v-if="patrol.patrolid >= 0"
            class="text-center print:hidden px-5 py-2.5 text-xs font-semibold text-slate-500 border-t flex justify-between"
          >
            <p>Average age</p>
            <p>
              {{ formatAge(averageAge(patrol.patrolid).toFixed(1)) }}
            </p>
          </div>
        </template>
      </draggable>
    </div>

    <div class="flex-1"></div>

    <div class="flex items-center text-sm text-gray-500 print:hidden">
      <div class="flex-1">
        <p>
          To change the six/patrol a member, drag and drop them into the correct
          column and position.
        </p>
        <p>Click or tap a member to change their rank.</p>
      </div>
      <div class="text-right">
        <p class="text-purple text-right font-medium">
          No changes will be saved until you click the "Save Changes" button.
        </p>
        <div class="text-xs flex gap-4 justify-end">
          <p class="text-xs">Built by City of Newcastle Scouts Digital SASU</p>
          <a
            target="_blank"
            href="https://github.com/newcastlescouts/patrolplanner"
            class="text-xs underline"
            >Source Code</a
          >
        </div>
      </div>
    </div>
  </div>
</template>
