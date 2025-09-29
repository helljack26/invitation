import dynamic from 'next/dynamic'

// import the real component, not the page file:
const InvitationPage = dynamic(
  () => import('../components/InvitationPage'),
  { ssr: false }
)

export default function Home() {
  return <InvitationPage /* you can pass a code if you know one */ />
}