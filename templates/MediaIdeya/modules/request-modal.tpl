<div class="mi-request-modal" data-request-modal aria-hidden="true">
	<img class="mi-request-modal__background" src="{THEME}/images/modal/background.png" width="1920" height="1561" alt="" aria-hidden="true">
	<div class="mi-request-modal__backdrop" data-request-modal-close></div>
	<section class="mi-request-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="mi-request-modal-title">
		<button class="mi-request-modal__close" type="button" aria-label="Закрыть" data-request-modal-close><img src="{THEME}/images/icons/modal-close.svg" width="36" height="36" alt=""></button>
		<div class="mi-request-modal__copy">
			<div class="mi-request-modal__heading"><h2 id="mi-request-modal-title">Давайте обсудим вашу задачу</h2><p>Свяжитесь с нами удобным для вас способом или оставьте заявку на консультацию. Вместе разберём вашу задачу и найдём решение.</p></div>
			<div class="mi-request-modal__messengers"><strong>Напишите нам:</strong>{custom category="8" template="modules/request-modal-messengers" limit="1" order="date" sort="asc" cache="no"}</div>
		</div>
		<form class="mi-request-modal__form" action="{THEME}/../../index.php?do=feedback" method="post" data-request-modal-form>
			<label class="mi-request-modal__field mi-request-modal__field--wide">ФИО*<input name="name" type="text" placeholder="ФИО" autocomplete="name" required></label>
			<div class="mi-request-modal__field-row"><label class="mi-request-modal__field mi-request-modal__field--phone">Телефон*<span class="mi-request-modal__phone-input"><b>+7</b><input name="phone" type="tel" placeholder="(000) 000-00-00" autocomplete="tel" required></span></label><label class="mi-request-modal__field">Почта*<input name="mail" type="email" placeholder="Почта" autocomplete="email" required></label></div>
			<label class="mi-request-modal__field mi-request-modal__field--wide">Описание задачи<textarea name="message" rows="1" placeholder="Описание задачи"></textarea></label>
			<div class="mi-request-modal__consents"><label><input type="checkbox" required><span>Ознакомлен(а) и принимаю условия <a href="{THEME}/../../politika-privatnosti.html">Политики обработки персональных данных</a></span></label><label><input type="checkbox" required><span>Даю свое согласие на <a href="{THEME}/../../politika-privatnosti.html">обработку персональных данных</a></span></label></div>
			<button class="mi-btn mi-request-modal__submit" type="submit">Отправить заявку</button>
		</form>
		<div class="mi-request-modal__loader" data-request-modal-loader role="status" aria-live="polite" aria-label="Отправка заявки"><img src="{THEME}/images/loading/wreath-left.svg" width="143" height="268" alt=""><span data-request-modal-progress>0%</span><img src="{THEME}/images/loading/wreath-right.svg" width="143" height="268" alt=""></div>
	</section>
</div>
