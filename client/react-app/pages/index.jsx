// pages/index.js
import React, { useState } from 'react';
import dynamic from 'next/dynamic';
import EnvelopeLoader from '../components/EnvelopeLoader';

// Replace with your actual invitation page component
const InvitationPage = dynamic(
  () => import('../invitation/[uniquePath]'),
  { ssr: false }
);

export default function Home() {
  const [loaded, setLoaded] = useState(false);

  return (
    <>
      {!loaded && <EnvelopeLoader onComplete={() => setLoaded(true)} />}
      {loaded && <InvitationPage />}
    </>
  );
}
