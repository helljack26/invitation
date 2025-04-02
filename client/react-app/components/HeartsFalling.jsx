import { useEffect, useRef } from "react";
import { observer } from "mobx-react";
import GlobalState from "../stores/GlobalState";

export const HeartsFalling = observer(() => {
	const leafsFallingRef = useRef(null);

	useEffect(() => {
		if (typeof window === "undefined" || !leafsFallingRef.current) {
			return;
		}
	}, []);

	return (
		<div className="falling_hearts">
			<div class="snowflakes">
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>{" "}
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
				<div class="snowflake">
					<img
						src="https://i.pinimg.com/originals/96/c7/8b/96c78bc8ab873498b763798793d64f62.png"
						width="25"
					/>
				</div>
			</div>
		</div>
	);
});
