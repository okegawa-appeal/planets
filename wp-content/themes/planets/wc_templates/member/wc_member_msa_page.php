<?php
/**
 * Template for Mutiple Shipping Member Page.
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
global $usces;
$usces_members = $usces->get_member();
?>
	<div id="primary" class="site-content">
		<div id="content" class="member-page" role="main">

			<article class="post" id="wc_member_msa">

				<h1 class="member_page_title"><?php echo apply_filters( 'msa_filter_gift_label', 'ギフト用' ); ?>配送先</h1>

				<div id="memberpages">
					<table>
						<tr>
							<th scope="row"><?php esc_html_e( 'member number', 'usces' ); ?></th>
							<td class="num"><?php echo esc_html( $usces_members['ID'] ); ?></td>
							<th><?php esc_html_e( 'Strated date', 'usces' ); ?></th>
							<td><?php echo esc_html( mysql2date( __( 'Y/m/d' ), $usces_members['registered'] ) ); ?></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Full name', 'usces' ); ?></th>
							<td><?php usces_localized_name( $usces_members['name1'], $usces_members['name2'] ); ?></td>
							<?php if ( usces_is_membersystem_point() ) : ?>
								<th><?php esc_html_e( 'The current point', 'usces' ); ?></th>
								<td class="num"><?php echo esc_html( $usces_members['point'] ); ?></td>
							<?php else : ?>
								<th class="space">&nbsp;</th>
								<td class="space">&nbsp;</td>
							<?php endif; ?>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'e-mail adress', 'usces' ); ?></th>
							<td><?php echo esc_html( $usces_members['mailaddress1'] ); ?></td>
							<?php $html_reserve = '<th class="space">&nbsp;</th><td class="space">&nbsp;</td>'; ?>
							<?php echo apply_filters( 'usces_filter_memberinfo_page_reserve', $html_reserve, usces_memberinfo( 'ID', 'return' ) ); // phpcs:ignore ?>
						</tr>
					</table>

					<ul class="member_submenu">
						<li class="edit_member"><a href="<?php echo esc_url( USCES_MEMBER_URL ); ?>">会員ページトップへ戻る</a></li>
						<?php do_action( 'usces_action_member_submenu_list' ); ?>
					</ul>

					<div class="header_explanation">
						<?php do_action( 'usces_action_memberinfo_page_header' ); ?>
					</div>

					<h3><?php echo apply_filters( 'msa_filter_gift_label', 'ギフト用' ); ?>配送先を登録</h3>
					<div class="msa_area">
						<div class="msa_total">登録件数<span id="msa_num"></span>件<input id="new_destination" type="button" value="新しい配送先を追加する" /></div>
						<?php
						if ( isset( $_GET['msa_backurl'] ) ) : // phpcs:ignore
							$cusurl = str_replace( 'customerinfo=1', 'customerinfo=msa', USCES_CUSTOMER_URL );
							?>

							<div class="return_navi"><a href="<?php echo esc_url( $cusurl ); ?>">配送先設定へ戻る</a></div>
							<div class="allocation_dialog_exp">
								<ol>
									<li>「新しい配送先を登録する」を押すと、新規登録画面になります。</li>
									<li>お名前やご住所などの入力が終わりましたら「登録」を押して下さい。</li>
									<li>登録がすべて終わりましたら、画面下の「配送先設定に戻る」を押して下さい。</li>
								</ol>
								※ （ご本人）と書かれた配送先は削除できません。登録した配送先を変更・削除する場合は「編集する配送先を選ぶ」から（ご本人）以外のデータを選択して下さい。
							</div>

						<?php else : ?>

							<div class="allocation_dialog_exp">
								<ol>
									<li>「新しい配送先を登録する」を押すと、新規登録画面になります。</li>
									<li>お名前やご住所などの入力が終わりましたら「登録」を押して下さい。</li>
								</ol>
								※ （ご本人）と書かれた配送先は削除できません。登録した配送先を変更・削除する場合は「編集する配送先を選ぶ」から（ご本人）以外のデータを選択して下さい。
							</div>

						<?php endif; ?>

						<div class="msa_operation">
							<label for="destination" class="destination_label">編集する配送先を選ぶ</label><select id="destination"></select>
							<?php do_action( 'msa_action_memberinfo_page_operation' ); ?>
						</div>

						<div class="msa_field_block">
							<div class="msa_title">配送先 : <span id="destination_title" class="msa_title_inner"></span></div>
							<?php msa_admin_custom_delivery_field_input( 'name_pre' ); ?>
							<div class="msa_field">
								<label for="msa_company">法人名</label>
								<input id="msa_company" name="msa_company" type="text" />
							</div>
							<div class="msa_field">
								<label for="msa_name">氏名（必須）</label>
								<span class="member_name">姓</span><input id="msa_name" name="msa_name" type="text" />
								<span class="member_name">名</span><input id="msa_name2" name="msa_name2" type="text" />
								<span id="name_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_furigana">フリガナ</label>
								<span class="member_furigana">セイ</span><input id="msa_furigana" name="msa_furigana" type="text" />
								<span class="member_furigana">メイ</span><input id="msa_furigana2" name="msa_furigana2" type="text" />
							</div>
							<?php msa_admin_custom_delivery_field_input( 'name_after' ); ?>
							<div class="msa_field">
								<label for="msa_zip">郵便番号（必須）</label><input id="msa_zip" name="msa_zip" type="text" />
								<?php
								if ( isset( $usces->options['address_search'] ) && 'activate' === $usces->options['address_search'] ) {
									echo "<input type='button' id='search_zipcode' class='search-zipcode button' value='住所検索' onclick=\"AjaxZip3.zip2addr('msa_zip', '', 'msa_pref', 'msa_address1');\">";
								}
								?>
								<span id="zip_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_pref">都道府県（必須）</label>
								<select name="msa_pref" id="msa_pref" class="pref">
									<option value="0">--選択--</option>
									<option value="北海道">北海道</option>
									<option value="青森県">青森県</option>
									<option value="岩手県">岩手県</option>
									<option value="宮城県">宮城県</option>
									<option value="秋田県">秋田県</option>
									<option value="山形県">山形県</option>
									<option value="福島県">福島県</option>
									<option value="茨城県">茨城県</option>
									<option value="栃木県">栃木県</option>
									<option value="群馬県">群馬県</option>
									<option value="埼玉県">埼玉県</option>
									<option value="千葉県">千葉県</option>
									<option value="東京都">東京都</option>
									<option value="神奈川県">神奈川県</option>
									<option value="新潟県">新潟県</option>
									<option value="富山県">富山県</option>
									<option value="石川県">石川県</option>
									<option value="福井県">福井県</option>
									<option value="山梨県">山梨県</option>
									<option value="長野県">長野県</option>
									<option value="岐阜県">岐阜県</option>
									<option value="静岡県">静岡県</option>
									<option value="愛知県">愛知県</option>
									<option value="三重県">三重県</option>
									<option value="滋賀県">滋賀県</option>
									<option value="京都府">京都府</option>
									<option value="大阪府">大阪府</option>
									<option value="兵庫県">兵庫県</option>
									<option value="奈良県">奈良県</option>
									<option value="和歌山県">和歌山県</option>
									<option value="鳥取県">鳥取県</option>
									<option value="島根県">島根県</option>
									<option value="岡山県">岡山県</option>
									<option value="広島県">広島県</option>
									<option value="山口県">山口県</option>
									<option value="徳島県">徳島県</option>
									<option value="香川県">香川県</option>
									<option value="愛媛県">愛媛県</option>
									<option value="高知県">高知県</option>
									<option value="福岡県">福岡県</option>
									<option value="佐賀県">佐賀県</option>
									<option value="長崎県">長崎県</option>
									<option value="熊本県">熊本県</option>
									<option value="大分県">大分県</option>
									<option value="宮崎県">宮崎県</option>
									<option value="鹿児島県">鹿児島県</option>
									<option value="沖縄県">沖縄県</option>
								</select><span id="pref_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_address1">市区町村（必須）</label>
								<input id="msa_address1" name="msa_address1" type="text" />
								<span id="address1_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_address2">番地（必須）</label>
								<input id="msa_address2" name="msa_address2" type="text" />
								<span id="address2_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_address3">ビル名など</label>
								<input id="msa_address3" name="msa_address3" type="text" />
							</div>
							<div class="msa_field">
								<label for="msa_tel">電話番号（必須）</label>
								<input id="msa_tel" name="msa_tel" type="text" />
								<span id="tel_message" class="msa_message"></span>
							</div>
							<div class="msa_field">
								<label for="msa_fax">FAX番号</label>
								<input id="msa_fax" name="msa_fax" type="text" />
								<span id="fax_message" class="msa_message"></span>
							</div>
							<?php msa_admin_custom_delivery_field_input( 'fax_after' ); ?>
							<div class="msa_field">
								<label for="msa_note">備考</label>
								<textarea id="msa_note" name="msa_note"></textarea>
							</div>
							<input name="_wcnonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'msa-nonce' ) ); ?>"/>
							<input name="member_id" type="hidden" value="<?php echo esc_attr( $usces_members['ID'] ); ?>"/>
							<div id="msa_button">
								<input name="add_destination" id="add_destination" type="button" value="登録" />
								<input name="edit_destination" id="edit_destination" type="button" value="更新" />
								<input name="cancel_destination" id="cancel_destination" type="button" value="キャンセル" />
								<input id="del_destination" type="button" value="削除" />
							</div>
							<div id="msa_loading"></div>
						</div>
					</div>

					<div class="footer_explanation">
						<?php do_action( 'msa_action_memberinfo_page_footer' ); ?>
					</div>

				</div>

			</article><!-- .post -->

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
