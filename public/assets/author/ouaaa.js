
const blocks = [
  {
    type: "art",
    lines: [
      "██████╗ ██╗   ██╗██╗██╗     ████████╗",
      "██╔══██╗██║   ██║██║██║     ╚══██╔══╝",
      "██████╔╝██║   ██║██║██║        ██║",
      "██╔══██╗██║   ██║██║██║        ██║",
      "██████╔╝╚██████╔╝██║███████╗   ██║",
      "╚═════╝  ╚═════╝ ╚═╝╚══════╝   ╚═╝",
    ],
  },
  {
    type: "text",
    lines: [
      "",
      "Built by Ricafort, Roland Josh M.",
      "GitHub: https://github.com/Ouaaaa",
    ],
  },
  {
    type: "art",
    lines: [
      "██████╗  █████╗ ███╗   ██╗",
      "██╔══██╗██╔══██╗████╗  ██║",
      "██║  ██║███████║██╔██╗ ██║",
      "██║  ██║██╔══██║██║╚██╗██║",
      "██████╔╝██║  ██║██║ ╚████║",
      "╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═══╝",
    ],
  },
  {
    type: "text",
    lines: [
      "",
      "Maintained by Dan",
      "GitHub: https://github.com/1wonder29",
    ],
  },
];

const colors = ["#0f0", "#f00", "#0ff", "#ff0", "#f0f"];
const baseStyle = "font-family:monospace;font-size:15px;font-weight:bold;";

// Printed once per page. It used to clear and redraw the console every 140 ms,
// which kept the browser busy on every page (and wiped real error messages).
(() => {
  const color = colors[2];

  blocks.forEach((block) => {
    block.lines.forEach((line) => {
      const isArt = block.type === "art";

      console.log(
        "%c" + line,
        `
          color: ${isArt ? color : "#9a9a9a"};
          ${baseStyle}
          ${isArt ? "text-shadow:0 0 8px " + color + ";" : ""}
        `
      );
    });
  });

})();
