// hooks/useDivider.js
import { useState, useEffect } from "react";

function displaceMap(height, displace, roughness, power) {
	const points = [];
	points[0] = height / 2 + Math.random() * displace * 2 - displace;
	points[power] = height / 2 + Math.random() * displace * 2 - displace;
	displace *= roughness;

	for (let i = 1; i < power; i *= 2) {
		for (let j = power / i / 2; j < power; j += power / i) {
			const left = points[j - power / i / 2];
			const right = points[j + power / i / 2];
			points[j] =
				(left + right) / 2 + Math.random() * displace * 2 - displace;
		}
		displace *= roughness;
	}

	return points;
}

function line(width, points) {
	const sep = width / (points.length - 1);
	return points.map((val, i) => [i * sep, val]);
}

function convertPath(width, height, points) {
	const [first, ...rest] = points;
	let path = `M ${first[0]} ${first[1]}`;
	rest.forEach(([x, y]) => {
		path += ` L ${x} ${y}`;
	});
	path += ` L ${width} ${height} L 0 ${height} Z`;
	return path;
}

function genSvg(width, height, path) {
	return `
    <svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path fill="currentColor" d="${path}"></path>
    </svg>
  `;
}

export function useDivider({
	width = 1200,
	height = 100,
	iterations = 6,
	roughness = 0.8,
}) {
	const [svgString, setSvgString] = useState("");

	useEffect(() => {
		const segments = Math.pow(2, iterations);
		const pts = displaceMap(height, height / 4, roughness, segments);
		const coords = line(width, pts);
		const path = convertPath(width, height, coords);
		setSvgString(genSvg(width, height, path));
	}, [width, height, iterations, roughness]);

	return svgString;
}
