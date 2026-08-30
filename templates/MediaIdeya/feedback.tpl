<article class="mi-contact-page">
	<section class="mi-contact-intro" aria-labelledby="mi-contact-title">
		{custom category="8" template="modules/contact-details" limit="1" order="date" sort="asc" cache="no"}
	</section>

	<section class="mi-contact-location" aria-label="Адрес офиса">
		{custom category="8" template="modules/contact-address" limit="1" order="date" sort="asc" cache="no"}
	</section>

	<section class="mi-project-contact" aria-labelledby="mi-project-contact-title">
		<div class="mi-project-contact__card">
			<div class="mi-project-contact__column" aria-hidden="true">
				<img src="{THEME}/images/pages/contact/column.png" width="1568" height="2734" alt="" loading="lazy" decoding="async">
			</div>

			{custom category="8" template="modules/contact-project-copy" limit="1" order="date" sort="asc" cache="no"}

			<div class="mi-project-form">
				<div class="mi-project-form__field mi-project-form__field--wide">
					<label for="name">ФИО*</label>
					<input type="text" maxlength="35" name="name" id="name" placeholder="ФИО" autocomplete="name" required>
				</div>
				<div class="mi-project-form__row">
					<div class="mi-project-form__field mi-project-form__field--phone">
						<label for="subject">Телефон*</label>
						<span class="mi-project-phone-input"><b>+7</b><input type="tel" maxlength="45" name="subject" id="subject" placeholder="(000) 000-00-00" autocomplete="tel" data-mi-phone-mask required></span>
					</div>
					<div class="mi-project-form__field">
						<label for="mail">Почта*</label>
						<input type="email" maxlength="35" name="mail" id="mail" placeholder="Почта" autocomplete="email" required>
					</div>
				</div>
				<div class="mi-project-form__field mi-project-form__field--wide">
					<label for="message">Описание задачи</label>
					<textarea name="message" id="message" rows="2" placeholder="Описание задачи" required></textarea>
				</div>

				<div class="mi-project-form__consents">
					<label><input type="checkbox" name="privacy_accept" value="1" required><span>Ознакомлен(а) и принимаю условия <a href="{THEME}/../../politika-privatnosti.html">Политики обработки персональных данных</a></span></label>
					<label><input type="checkbox" name="personal_accept" value="1" required><span>Даю свое согласие на <a href="{THEME}/../../politika-privatnosti.html">обработку персональных данных</a></span></label>
				</div>

				<div class="mi-project-form__engine">
					<label class="mi-project-form__recipient">{{To}} {recipient}</label>
					[attachments]<label>{{Attachments}}: <input name="attachments[]" type="file" multiple></label>[/attachments]
					[recaptcha]<div>{recaptcha}</div>[/recaptcha]
					[question]<label for="question_answer">{{Question}}: {question}</label><input placeholder="{{Answer}}" type="text" name="question_answer" id="question_answer" required>[/question]
					[sec_code]<div class="c-captcha">{code}<input placeholder="{{Enter the code}}" title="{{Enter the code from the image}}" type="text" name="sec_code" id="sec_code" required></div>[/sec_code]
				</div>

				<button class="mi-btn mi-project-form__submit" type="submit" name="send_btn">Отправить заявку</button>
			</div>
		</div>
	</section>
</article>
