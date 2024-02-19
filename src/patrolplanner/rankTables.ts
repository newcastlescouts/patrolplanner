import type { RankLiteral } from "@/patrolplanner/types";

const rankLabels: RankLiteral = {
  cubs: {
    0: "Cub",
    1: "Seconder",
    2: "Sixer",
    3: "Senior Sixer",
  },
  scouts: {
    0: "Scout",
    1: "Assistant Patrol Leader",
    2: "Patrol Leader",
    3: "Senior Patrol Leader",
  },
};

const rankAbbreviations: RankLiteral = {
  cubs: {
    0: "",
    1: "2nd",
    2: "6er",
    3: "Snr",
  },
  scouts: {
    0: "",
    1: "APL",
    2: "PL",
    3: "SPL",
  },
};

export { rankLabels, rankAbbreviations };
