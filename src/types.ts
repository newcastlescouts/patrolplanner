export interface Section {
  name: string;
  sectionid: number;
  groupid: number;
  member_permission: number;
  colour: string;
  section: string;
}

export interface SectionPayload {
  group: string;
  section: Section;
}

export interface Member {
  name: string;
  forename: string;
  surname: string;
  patrolid: number;
  scoutid: number;
  active: boolean;
  role_level_label: string;
  patrol_label: string;
  photo_url: string;
  rank: number;
  rank_abbr: string;
  age: string;
}

export interface Patrol {
  name: string;
  patrolid: number;
}

export interface RankLiteral {
  [key: string]: RankLabel;
}

export interface RankLabel {
  [key: number]: string;
}

export enum SaveQueueState {
  WAITING = "WAITING",
  SAVING = "SAVING",
  SAVED = "SAVED",
  ERROR = "ERROR",
}

export enum SaveQueueType {
  PATROL_CHANGE = "Patrol",
  RANK_CHANGE = "Rank",
}

export interface SaveQueueEntry {
  member: Member;
  value: any;
  state: SaveQueueState;
  type: SaveQueueType;
}
