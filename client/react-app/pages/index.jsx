export default function Home() {
	useEffect(() => {
		if (typeof window === "undefined") return;
	}, []);
	return (
		<>
			<span>index</span>
		</>
	);
}
