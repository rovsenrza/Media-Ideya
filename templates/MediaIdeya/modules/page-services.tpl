<article class="mi-service-detail" data-services-page data-figma-node="47:913">
	{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-hero" limit="1" order="date" sort="asc" cache="no"}

	<section class="mi-service-detail__content" aria-label="Об услуге">
		<div class="mi-service-detail__stage">
			{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-editorial" limit="1" order="date" sort="asc" cache="no"}
			{custom category="2" tags="product-placement" template="modules/service-case-bubble" limit="5" order="date" sort="desc" cache="no"}
		</div>
	</section>

	<section class="mi-service-detail__request" id="service-request" aria-labelledby="mi-service-request-title">
		<div class="mi-service-detail__request-card" data-service-reveal>
			<div class="mi-service-detail__request-column" aria-hidden="true">
				<img src="{THEME}/images/pages/services/column.png" alt="" width="1568" height="2734" loading="lazy" decoding="async">
			</div>

			{custom category="8" template="modules/project-request-service" limit="1" order="date" sort="asc" cache="no"}

			<form class="mi-service-detail__form" action="{THEME}/../../index.php?do=feedback" method="post" data-service-form>
				<input type="hidden" name="do" value="feedback">
				{custom category="4" xfields="service_detail_enabled|1" template="modules/service-detail-form-context" limit="1" order="date" sort="asc" cache="no"}
				<input type="hidden" name="contact_channel" value="" data-service-channel-input>

				<label class="mi-service-detail__field">
					<span>ФИО*</span>
					<input type="text" name="name" placeholder="ФИО" autocomplete="name" required>
				</label>

				<div class="mi-service-detail__field-row">
					<label class="mi-service-detail__field mi-service-detail__field--phone">
						<span>Телефон*</span>
						<input type="tel" name="phone" placeholder="+7 (000) 000-00-00" autocomplete="tel" required>
					</label>
					<label class="mi-service-detail__field">
						<span>Почта*</span>
						<input type="email" name="mail" placeholder="Почта" autocomplete="email" required>
					</label>
				</div>

				<label class="mi-service-detail__field">
					<span>Описание задачи</span>
					<textarea name="message" rows="1" placeholder="Описание задачи"></textarea>
				</label>

				<div class="mi-service-detail__consents">
					<label>
						<input type="checkbox" name="privacy_policy" value="1" required>
						<span>Ознакомлен(а) и принимаю условия <a href="{THEME}/../../politika-privatnosti.html">Политики обработки персональных данных</a></span>
					</label>
					<label>
						<input type="checkbox" name="personal_data" value="1" required>
						<span>Даю своё согласие на <a href="{THEME}/../../politika-privatnosti.html">обработку персональных данных</a></span>
					</label>
				</div>

				<button class="mi-service-detail__submit" type="submit">Отправить заявку</button>
			</form>
		</div>
	</section>
</article>
