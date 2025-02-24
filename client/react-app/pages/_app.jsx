// import NextNprogress from 'nextjs-progressbar'
import React from "react";
import "../styles/styles.css";
import "@csstools/normalize.css";

export default function MyApp({ Component, pageProps }) {
	return (
		<>
			<Component {...pageProps} />
		</>
	);
}
