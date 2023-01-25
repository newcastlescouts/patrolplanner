import type { Member } from "@/types";

export const imageReplace = (event: any, element: Member) => {
  event.target.src = `https://api.dicebear.com/5.x/fun-emoji/svg?seed=${element.scoutid}`;
};
