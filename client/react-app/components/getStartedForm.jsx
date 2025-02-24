import { useState } from 'react'
import Image from 'next/image'
import { useForm } from 'react-hook-form';

import I from '../img/images'

import style from '../styles/main.module.scss'

export const GetStartedForm = () => {
	const { register, handleSubmit, setValue, reset, formState: { errors }, } = useForm();
	const [isSuccessDisplay, setSuccessDisplay] = useState(false);
	const [isSuccess, setSuccess] = useState(false);

	const onSubmit = (data) => {
		console.log('кайф', data)
		reset({ ...data })
		setValue('userName', '')
		setValue('userEmail', '')

		if (data) {
			setSuccess(true)
			setTimeout(() => {
				setSuccessDisplay(true)
			}, 400);
			setTimeout(() => {
				setSuccessDisplay(false)
			}, 10000);
			setTimeout(() => {
				setSuccess(false)
			}, 10400);
		}
		return
	};

	return (
		<div
			id='getStartedForm'
			className={style.getStartedForm}
			data-scroll-section
		>
			<div className={style.getStartedForm_block}
				data-scroll
				data-scroll-speed={0.2}
				data-scroll-delay={1}
				data-scroll-position="top"
				data-scroll-direction="vertical"
			>

				<div className={style.getStartedForm_col}>
					<h2 >Get Started Today!</h2>
					<p >
						Learn more about how you can save our planet's nature. From smart consumption to switching to renewable energy, each of us can do our part to save the planet.
					</p>
				</div>

				<form
					onSubmit={handleSubmit(onSubmit)}
					className={style.getStartedForm_form}>
					<h3>Log In</h3>
					<div className={style.getStartedForm_form_input}>
						<input
							type={'text'}
							placeholder='Name'
							{...register('userName', {
								required: 'Required field',
							})}
							style={{
								borderColor: errors.userName !== undefined && 'rgb(244, 59, 59)',
							}}
						/>
					</div>

					<div className={style.getStartedForm_form_input}>
						<input
							type={'text'}
							placeholder='Email'
							{...register('userEmail', {
								required: 'Required field',
								pattern: { value: /\S+@\S+\.\S+/, message: "Email not valid" }
							})}
							style={{
								borderColor: errors.userEmail !== undefined && 'rgb(244, 59, 59)',
							}}
						/>
						{errors.userEmail && <p className={style.errorMessage}>{errors.userEmail.message}</p>}
					</div>

					<button type="submit">
						Book a demo
					</button>

					<div className={style.getStartedForm_success}
						style={{
							opacity: isSuccessDisplay === true ? 1 : 0,
							display: isSuccess === true ? 'flex' : 'none',
						}}
					>Let's begin!</div>

					<div className={style.getStartedForm_form_leaf}>
						<Image src={I.formLeafs} priority />
					</div>

				</form>
			</div>

		</div>
	)
}

