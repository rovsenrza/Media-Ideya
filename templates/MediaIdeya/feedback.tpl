<article class="mi-contact-page">
	<section class="mi-contact-intro" aria-labelledby="mi-contact-title">
		<nav class="mi-breadcrumbs" aria-label="Хлебные крошки">
			<a href="{THEME}/../../">Главная</a>
			<span aria-hidden="true">/</span>
			<span>Контакты</span>
		</nav>

		<div class="mi-contact-intro__body">
			<h1 class="mi-contact-intro__title" id="mi-contact-title">Контакты</h1>
			{custom category="8" template="modules/contact-details" limit="1" order="date" sort="asc" cache="no"}
			<img class="mi-contact-intro__wreath" src="{THEME}/images/pages/contact/wreath.png" width="630" height="556" alt="" decoding="async">
		</div>
	</section>

	<section class="mi-contact-location" aria-label="Адрес офиса">
		{custom category="8" template="modules/contact-address" limit="1" order="date" sort="asc" cache="no"}
		<div class="mi-contact-location__map">
			<img src="{THEME}/images/pages/contact/map.png" width="1920" height="594" alt="Карта: офис Media Ideya, Брянск, проспект Станке Димитрова, 54А" loading="lazy" decoding="async">
		</div>
	</section>

	<section class="mi-project-contact" aria-labelledby="mi-project-contact-title">
		<div class="mi-project-contact__card">
			<div class="mi-project-contact__column" aria-hidden="true">
				<img src="{THEME}/images/pages/contact/column.png" width="1568" height="2734" alt="" loading="lazy" decoding="async">
			</div>

			<div class="mi-project-contact__copy">
				<h2 id="mi-project-contact-title">Давайте обсудим<br>вашу задачу</h2>
				<p>Свяжитесь с нами удобным для вас способом или оставьте заявку на консультацию. Вместе разберём вашу задачу и найдём решение.</p>
				<div class="mi-project-contact__channels">
					<strong>Напишите нам:</strong>
					<div>
						<a href="https://t.me/mediaideya" rel="noopener noreferrer">Telegram <img src="{THEME}/images/pages/contact/telegram.svg" width="23" height="20" alt=""></a>
						<a href="https://wa.me/79532843200" rel="noopener noreferrer">Whatsapp <img src="{THEME}/images/pages/contact/whatsapp.svg" width="24" height="24" alt=""></a>
					</div>
				</div>
			</div>

			<div class="mi-project-form">
				<div class="mi-project-form__field mi-project-form__field--wide">
					<label for="name">ФИО*</label>
					<input type="text" maxlength="35" name="name" id="name" placeholder="ФИО" autocomplete="name" required>
				</div>
				<div class="mi-project-form__row">
					<div class="mi-project-form__field mi-project-form__field--phone">
						<label for="subject">Телефон*</label>
						<input type="tel" maxlength="45" name="subject" id="subject" placeholder="+7 (000) 000-00-00" autocomplete="tel" inputmode="tel" required>
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
