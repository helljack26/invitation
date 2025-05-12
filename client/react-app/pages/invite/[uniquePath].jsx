// client/react-app/pages/invite/[uniquePath].jsx
import { useRouter } from 'next/router'
import InvitationPage from '../../components/InvitationPage'

export default function InviteWrapper() {
  const { uniquePath } = useRouter().query


  // You can rename the prop however you like:
  return <InvitationPage inviteCode={uniquePath} />
}
