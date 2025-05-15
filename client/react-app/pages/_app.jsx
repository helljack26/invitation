// pages/_app.jsx
import React from 'react';
import '@csstools/normalize.css';
import '../styles/main.scss';

export default function MyApp({ Component, pageProps }) {
  return (
      <Component {...pageProps} />
  );
}
