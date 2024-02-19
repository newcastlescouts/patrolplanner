import { ofetch } from "ofetch";

const base = import.meta.env.VITE_BASE_URL || "/";

let apiFetch = ofetch.create({ baseURL: base });

function initSession() {
  // client only
  if (typeof window === "undefined") return;

  const queryString = new URLSearchParams(window.location.search);
  if (queryString.has("session")) {
    localStorage.setItem("session", queryString.get("session")!!);
  }

  if (localStorage.getItem("session")) {
    apiFetch = ofetch.create({
      baseURL: base,
      headers: {
        Authorization: `X-NCLScouts-Session ${localStorage.getItem("session")}`,
      },
    });
  }
}

initSession();

export default apiFetch;
