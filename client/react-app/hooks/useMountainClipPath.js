// hooks/useMountainClipPath.js
import { useMemo } from "react";
import { useDivider } from "./useDivider";

export function useMountainClipPath({ width = 1200, mountainHeight = 80 }) {
  // 1) Generate the full divider SVG path (which by default does:
  //    M x0 y0 L x1 y1 … L width,height L 0,height Z)
  const fullSvg = useDivider({ width, height: mountainHeight });

  // 2) Rip out the `d="…"` string
  const dMatch = fullSvg.match(/d="([^"]+)"/);
  const fullPath = dMatch ? dMatch[1] : "";

  // 3) Strip off the final "L width,height L 0,height Z" chunk
  //    so we're left with just "M x0 y0 L x1 y1 … L xn yn"
  const mountainOnly = fullPath.replace(/L\s*\d+\s+\d+\s*L\s*\d+\s+\d+\s*Z$/, "");

  // 4) Build the final clip-path string once
  return useMemo(() => {
    // We start at the **top-left** corner (0,0),
    // go to top-right (width,0),
    // drop down to the first mountain point at y=mountainHeight,
    // trace the mountain line,
    // then draw back to the bottom-left corner and close.
    const clipd = `
      M0 0 
      L${width} 0 
      L${width} ${mountainHeight} 
      ${mountainOnly} 
      L0 ${mountainHeight} 
      Z
    `.trim().replace(/\s+/g, " ");

    // wrap in the CSS path() fn
    return `path("${clipd}")`;
  }, [mountainOnly, width, mountainHeight]);
}
