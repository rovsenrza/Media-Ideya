<article class="box story">
	<div class="box_in">
		<h4 class="title h1">{header-title}</h4>
		<div class="addform">
			<ul class="ui-form">
				<li class="form-group">
					<label for="title" class="imp">{{Title}}</label>
					<input type="text" name="title" id="title" value="{title}" class="wide" required>
				</li>
				[urltag]
				<li class="form-group">
					<label for="alt_name" class="imp">{{Article URL}}</label>
					<input type="text" name="alt_name" id="alt_name" value="{alt-name}" class="wide">
				</li>
				[/urltag]
				<li class="form-group">
					<label for="category" class="imp">{{Category}}</label>
					{category}
				</li>
				<li class="form-group">
					<label><a href="#" onclick="$('.addvote').toggle();return false;"><span class="plus_icon circle"><span>+</span></span> {{Add voting}}</a></label>
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="vote_title" >{{Voting title}}</label>
					<input type="text" name="vote_title" id="vote_title" value="{votetitle}" class="wide" />
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="frage">{{Question}}</label>
					<input type="text" name="frage" id="frage" value="{frage}" class="wide" />
				</li>
				<li class="form-group addvote" style="display:none;">
					<label for="vote_body" >{{List of answers}}</label>
					<textarea name="vote_body" id="vote_body" rows="5" class="wide" placeholder="{{Each new line is a new answer variant}}">{votebody}</textarea><br><label class="form-check-label"><input class="form-check-input" type="checkbox" name="allow_m_vote" value="1" {allowmvote}><span>{{Allow multiple choice}}</span></label>
				</li>
				[allow-shortstory]
				<li class="form-group">
					<label for="short_story" class="imp">{{Introductory part}}</label>
					{shortarea}
				</li>
				[/allow-shortstory]
				[allow-fullstory]
				<li class="form-group">
					<label for="full_story">{{Full part}}</label>
					{fullarea}
				</li>
				[/allow-fullstory]
				<li class="form-group">
						{xfields}
				</li>
				<li class="form-group">
					<label for="alt_name">{{Keywords for a Tag Cloud}}</label>
					<input placeholder="{{Enter comma separated}}" type="text" name="tags" id="tags" value="{tags}" maxlength="150" autocomplete="off" class="wide">
				</li>
				<li class="form-group">
					<div class="admin_checkboxs">{admintag}</div>
				</li>
			[recaptcha]
				<li class="form-group">{recaptcha}</li>
			[/recaptcha]
			[question]
				<li class="form-group">
					<label for="question_answer">{question}</label>
					<input placeholder="{{Answer}}" type="text" name="question_answer" id="question_answer" class="wide" required>
				</li>
			[/question]
			</ul>
			<p style="margin: 20px 0 0 0;" class="grey"><span style="color: #e85319">*</span> - {{fields marked with asterisk are required.}}</p>
			<div class="form_submit">
				[sec_code]
					<div class="c-captcha">
						{sec_code}
						<input placeholder="{{Enter the code}}" title="{{Enter the code from the image}}" type="text" name="sec_code" id="sec_code" required>
					</div>
				[/sec_code]
				<button class="btn btn-big" type="submit" name="add"><b>{{Send}}</b></button>
				<button class="btn-border btn-big" onclick="preview()" type="submit" name="nview"><b>{{Preview}}</b></button>
			</div>
		</div>
	</div>
</article>