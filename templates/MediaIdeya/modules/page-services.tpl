<article class="mi-service-detail" data-services-page data-figma-node="47:913">
	{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-hero" limit="1" order="date" sort="asc" cache="no"}

	<section class="mi-service-detail__content" aria-label="Об услуге">
		<div class="mi-service-detail__stage">
			{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-editorial" limit="1" order="date" sort="asc" cache="no"}
			{custom category="2" tags="product-placement" template="modules/service-case-bubble" limit="5" order="date" sort="desc" cache="no"}
		</div>
	</section>

	<section class="mi-service-detail__request mi-project-contact" id="service-request" aria-labelledby="mi-project-contact-title">
		<div class="mi-project-contact__card" data-service-reveal>
			{custom category="8" template="modules/contact-project-copy" limit="1" order="date" sort="asc" cache="no"}

			<form class="mi-project-form" action="{THEME}/../../contact" method="post" data-service-form>
				<input type="hidden" name="do" value="feedback">
				{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-form-context" limit="1" order="date" sort="asc" cache="no"}

				<div class="mi-project-form__field">
					<label for="service-name">ФИО*</label>
					<input type="text" name="name" id="service-name" placeholder="ФИО" autocomplete="name" required>
				</div>

				<div class="mi-project-form__row">
					<div class="mi-project-form__field mi-project-form__field--phone">
						<label for="service-phone">Телефон*</label>
						<input type="tel" name="phone" id="service-phone" placeholder="(000) 000-00-00" autocomplete="tel" required>
					</div>
					<div class="mi-project-form__field">
						<label for="service-mail">Почта*</label>
						<input type="email" name="mail" id="service-mail" placeholder="Почта" autocomplete="email" required>
					</div>
				</div>

				<div class="mi-project-form__field">
					<label for="service-message">Описание задачи</label>
					<textarea name="message" id="service-message" rows="2" placeholder="Описание задачи" required></textarea>
				</div>

				<div class="mi-project-form__consents">
					<label>
						<input type="checkbox" name="privacy_policy" value="1" required>
						<span>Ознакомлен(а) и принимаю условия <a href="{THEME}/../../politika-privatnosti.html">Политики обработки персональных данных</a></span>
					</label>
					<label>
						<input type="checkbox" name="personal_data" value="1" required>
						<span>Даю своё согласие на <a href="{THEME}/../../politika-privatnosti.html">обработку персональных данных</a></span>
					</label>
				</div>

				<button class="mi-btn mi-project-form__submit" type="submit">Отправить заявку</button>
			</form>
		</div>
	</section>
</article>
