module.exports = {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx,vue}"],
  theme: {
    extend: {
      screens: {
        print: { raw: "print" },
      },
      colors: {
        purple: "#7413dc",
        teal: "#00a794",
        red: "#e22e12",
        navy: "#003982",
        green: "#23a950",
        blue: "#006ddf",
        yellow: "#ffe627",
      },
    },
  },
  plugins: [],
};
