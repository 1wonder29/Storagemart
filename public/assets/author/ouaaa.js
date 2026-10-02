
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

let tick = 0;

setInterval(() => {
  console.clear();

  const color = colors[tick % colors.length];

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

  tick++;
}, 140);
