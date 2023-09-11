<?php
/**
 * DL Seller Continuation Member List Class.
 *
 * @package WCEX DL Seller
 */
class ContinuationList {

	/**
	 * テーブル名
	 *
	 * @var string
	 */
	public $table;

	/**
	 * データ
	 *
	 * @var string
	 */
	public $rows;

	/**
	 * アクション
	 *
	 * @var string
	 */
	public $action;

	/**
	 * 表示開始行番号
	 *
	 * @var string
	 */
	public $startRow;

	/**
	 * 最大表示行数
	 *
	 * @var string
	 */
	public $maxRow;

	/**
	 * 現在のページNo
	 *
	 * @var string
	 */
	public $currentPage;

	/**
	 * 最初のページNo
	 *
	 * @var string
	 */
	public $firstPage;

	/**
	 * 前のページNo
	 *
	 * @var string
	 */
	public $previousPage;

	/**
	 * 次のページNo
	 *
	 * @var string
	 */
	public $nextPage;

	/**
	 * 最終ページNo
	 *
	 * @var string
	 */
	public $lastPage;

	/**
	 * ページネーション・ナビのボタンの数
	 *
	 * @var string
	 */
	public $naviMaxButton;

	/**
	 * ナヴィゲーションhtmlコード
	 *
	 * @var string
	 */
	public $dataTableNavigation;

	/**
	 * 表示データ期間
	 *
	 * @var string
	 */
	public $arr_period;

	/**
	 * サーチ条件
	 *
	 * @var string
	 */
	public $arr_search;

	/**
	 * 簡易絞込みSQL
	 *
	 * @var string
	 */
	public $searchSql;

	/**
	 * SKU絞り込み
	 *
	 * @var string
	 */
	public $searchSkuSql;

	/**
	 * サーチ表示スイッチ
	 *
	 * @var string
	 */
	public $searchSwitchStatus;

	/**
	 * データカラム
	 *
	 * @var string
	 */
	public $columns;

	/**
	 * 現在ソート中のフィールド
	 *
	 * @var string
	 */
	public $sortColumn;

	/**
	 * ソート保存カラム
	 *
	 * @var string
	 */
	public $sortOldColumn;

	/**
	 * 各フィールド毎の昇順降順スイッチ
	 *
	 * @var string
	 */
	public $sortSwitchs;

	/**
	 * ユーザー指定のヘッダ名
	 *
	 * @var string
	 */
	public $userHeaderNames;

	/**
	 * Action status
	 *
	 * @var string
	 */
	public $action_status;

	/**
	 * Action message
	 *
	 * @var string
	 */
	public $action_message;

	/**
	 * ページ制限
	 *
	 * @var string
	 */
	public $pageLimit;

	/**
	 * 処理ステータス
	 *
	 * @var string
	 */
	public $continue_status;

	/**
	 * Select query
	 *
	 * @var string
	 */
	public $selectSql;

	/**
	 * Join table query
	 *
	 * @var string
	 */
	public $joinTableSql;

	/**
	 * Meta data
	 *
	 * @var string
	 */
	public $con_meta;

	/**
	 * Current Page ID
	 *
	 * @var string
	 */
	public $currentPageIds;

	/**
	 * Period
	 *
	 * @var string
	 */
	public $period;

	/**
	 * Placeholder escape
	 *
	 * @var string
	 */
	public $placeholder_escape;

	/**
	 * Cookie
	 *
	 * @var array
	 */
	public $data_cookie;

	/**
	 * Construct.
	 */
	public function __construct() {
		global $wpdb;

		$this->listOption = get_option( 'usces_continuelist_option' );

		$this->table = $wpdb->prefix . 'usces_continuation';
		$this->set_column();
		$this->rows = array();

		$this->maxRow         = ( isset( $this->listOption['max_row'] ) ) ? $this->listOption['max_row'] : 50;
		$this->naviMaxButton  = 11;
		$this->firstPage      = 1;
		$this->pageLimit      = 'on';
		$this->action_status  = 'none';
		$this->action_message = '';

		$this->getCookie();
		$this->SetDefaultParam();
		$this->SetParamByQuery();

		$continue_status       = array(
			'continuation' => __( 'Continuation', 'dlseller' ),
			'cancellation' => __( 'Cancellation', 'dlseller' ),
		);
		$this->continue_status = apply_filters( 'dlseller_filter_continue_status', $continue_status, $this );

		$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
	}

	/**
	 * Set Column.
	 */
	public function set_column() {
		$columns                  = array();
		$columns['ID']            = __( 'ID', 'dlseller' );
		$columns['order_id']      = __( 'Order ID', 'dlseller' );
		$columns['deco_id']       = __( 'order number', 'usces' );
		$columns['mem_id']        = __( 'membership number', 'usces' );
		$columns['email']         = __( 'e-mail', 'usces' );
		$columns['name1']         = __( 'Last Name', 'usces' );
		$columns['name2']         = __( 'First Name', 'usces' );
		$columns['name3']         = __( 'Last Furigana', 'usces' );
		$columns['name4']         = __( 'First Furigana', 'usces' );
		$columns['limitofcard']   = __( 'Limit of Card(Month/Year)', 'dlseller' );
		$columns['price']         = __( 'Total Amount', 'usces' ) . '(' . __( usces_crcode( 'return' ), 'usces' ) . ')';
		$columns['acting']        = __( 'Settlement Supplier', 'dlseller' );
		$columns['payment_name']  = __( 'payment method', 'usces' );
		$columns['orderdate']     = __( 'Application Date', 'dlseller' );
		$columns['startdate']     = __( 'First Withdrawal Date', 'dlseller' );
		$columns['contractedday'] = __( 'Renewal Date', 'dlseller' );
		$columns['chargedday']    = __( 'Next Withdrawal Date', 'dlseller' );
		$columns['status']        = __( 'Status', 'dlseller' );
		$columns['condition']     = __( 'Condition', 'dlseller' );
		$columns                  = apply_filters( 'dlseller_filter_continue_memberlist_column', $columns, $this );
		$this->columns            = $columns;
	}

	/**
	 * Get Columns.
	 *
	 * @return array
	 */
	public function get_column() {
		return $this->columns;
	}

	/**
	 * Action.
	 *
	 * @return bool
	 */
	public function MakeTable() {

		$this->SetParam();

		switch ( $this->action ) {
			case 'searchOut':
				$this->SearchOut();
				$res = $this->GetRows();
				break;

			case 'searchIn':
			case 'returnList':
			case 'changeSort':
			case 'changePage':
			case 'refresh':
			default:
				$this->SearchIn();
				$res = $this->GetRows();
				break;
		}

		$this->SetNavi();
		$this->SetHeaders();

		if ( $res ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Default Parameters.
	 */
	public function SetDefaultParam() {
		$this->startRow     = isset( $this->data_cookie['startRow'] ) ? $this->data_cookie['startRow'] : 0;
		$this->currentPage  = isset( $this->data_cookie['currentPage'] ) ? $this->data_cookie['currentPage'] : 1;
		$this->sortColumn   = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : 'ID';
		$this->searchWhere  = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : '';
		$this->searchHaving = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : '';

		if ( isset( $this->data_cookie['arr_search'] ) ) {
			$this->arr_search = $this->data_cookie['arr_search'];
		} else {
			$arr_search       = array(
				'period'            => array( '', '' ),
				'order_column'      => array( '', '' ),
				'order_word'        => array( '', '' ),
				'order_word_term'   => array( 'contain', 'contain' ),
				'order_term'        => 'AND',
				'product_column'    => array( '', '' ),
				'product_word'      => array( '', '' ),
				'product_word_term' => array( 'contain', 'contain' ),
				'option_word'       => array( '', '' ),
				'product_term'      => 'AND',
			);
			$this->arr_search = apply_filters( 'dlseller_filter_continue_memberlist_arr_search', $arr_search, $this );
		}
		if ( isset( $this->data_cookie['sortSwitchs'] ) ) {
			$this->sortSwitchs = $this->data_cookie['sortSwitchs'];
		} else {
			foreach ( $this->columns as $key => $value ) {
				$this->sortSwitchs[ $key ] = 'DESC';
			}
		}

		$this->SetTotalRow();
	}

	/**
	 * Set Parameters.
	 */
	public function SetParam() {
		$this->startRow = ( $this->currentPage - 1 ) * $this->maxRow;
	}

	/**
	 * Set Parameters.
	 */
	public function SetParamByQuery() {
		global $wpdb;

		if ( isset( $_REQUEST['changePage'] ) ) {

			$this->action             = 'changePage';
			$this->currentPage        = (int) $_REQUEST['changePage'];
			$this->sortColumn         = ( isset( $data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs        = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames    = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->searchWhere        = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : $this->searchWhere;
			$this->searchHaving       = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : $this->searchHaving;
			$this->arr_search         = ( isset( $this->data_cookie['arr_search'] ) ) ? $this->data_cookie['arr_search'] : $this->arr_search;
			$this->totalRow           = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->selectedRow        = ( isset( $this->data_cookie['selectedRow'] ) ) ? $this->data_cookie['selectedRow'] : $this->selectedRow;
			$this->placeholder_escape = ( isset( $this->data_cookie['placeholder_escape'] ) ) ? $this->data_cookie['placeholder_escape'] : $this->placeholder_escape;

		} elseif ( isset( $_REQUEST['returnList'] ) ) {

			$this->action             = 'returnList';
			$this->currentPage        = ( isset( $this->data_cookie['currentPage'] ) ) ? $this->data_cookie['currentPage'] : $this->currentPage;
			$this->sortColumn         = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs        = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames    = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->searchWhere        = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : $this->searchWhere;
			$this->searchHaving       = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : $this->searchHaving;
			$this->arr_search         = ( isset( $this->data_cookie['arr_search'] ) ) ? $this->data_cookie['arr_search'] : $this->arr_search;
			$this->totalRow           = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->selectedRow        = ( isset( $this->data_cookie['selectedRow'] ) ) ? $this->data_cookie['selectedRow'] : $this->selectedRow;
			$this->placeholder_escape = ( isset( $this->data_cookie['placeholder_escape'] ) ) ? $this->data_cookie['placeholder_escape'] : $this->placeholder_escape;

		} elseif ( isset( $_REQUEST['changeSort'] ) ) {

			$this->action                           = 'changeSort';
			$this->sortOldColumn                    = $this->sortColumn;
			$this->sortColumn                       = str_replace( '`', '', $_REQUEST['changeSort'] );
			$this->sortColumn                       = str_replace( ',', '', $this->sortColumn );
			$this->sortSwitchs                      = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->sortSwitchs[ $this->sortColumn ] = ( 'ASC' === $_REQUEST['switch'] ) ? 'ASC' : 'DESC';
			$this->currentPage                      = ( isset( $this->data_cookie['currentPage'] ) ) ? $this->data_cookie['currentPage'] : $this->currentPage;
			$this->userHeaderNames                  = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->searchWhere                      = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : $this->searchWhere;
			$this->searchHaving                     = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : $this->searchHaving;
			$this->arr_search                       = ( isset( $this->data_cookie['arr_search'] ) ) ? $this->data_cookie['arr_search'] : $this->arr_search;
			$this->totalRow                         = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->selectedRow                      = ( isset( $this->data_cookie['selectedRow'] ) ) ? $this->data_cookie['selectedRow'] : $this->selectedRow;
			$this->placeholder_escape               = ( isset( $this->data_cookie['placeholder_escape'] ) ) ? $this->data_cookie['placeholder_escape'] : $this->placeholder_escape;

		} elseif ( isset( $_REQUEST['searchIn'] ) ) {

			$this->action                           = 'searchIn';
			$this->arr_search['order_column'][0]    = ! WCUtils::is_blank( $_REQUEST['search']['order_column'][0] ) ? str_replace( '`', '', $_REQUEST['search']['order_column'][0] ) : '';
			$this->arr_search['order_column'][1]    = ! WCUtils::is_blank( $_REQUEST['search']['order_column'][1] ) ? str_replace( '`', '', $_REQUEST['search']['order_column'][1] ) : '';
			$this->arr_search['order_word'][0]      = ! WCUtils::is_blank( $_REQUEST['search']['order_word'][0] ) ? trim( $_REQUEST['search']['order_word'][0]) : '';
			$this->arr_search['order_word'][1]      = ! WCUtils::is_blank( $_REQUEST['search']['order_word'][1] ) ? trim( $_REQUEST['search']['order_word'][1]) : '';
			$this->arr_search['order_word_term'][0] = isset( $_REQUEST['search']['order_word_term'][0] ) ? $_REQUEST['search']['order_word_term'][0] : 'contain';
			$this->arr_search['order_word_term'][1] = isset( $_REQUEST['search']['order_word_term'][1] ) ? $_REQUEST['search']['order_word_term'][1] : 'contain';
			if ( WCUtils::is_blank( $_REQUEST['search']['order_column'][0] ) ) {
				$this->arr_search['order_column'][1]    = '';
				$this->arr_search['order_word'][0]      = '';
				$this->arr_search['order_word'][1]      = '';
				$this->arr_search['order_word_term'][0] = 'contain';
				$this->arr_search['order_word_term'][1] = 'contain';
			}
			$this->arr_search['order_term']           = $_REQUEST['search']['order_term'];
			$this->arr_search['product_column'][0]    = ! WCUtils::is_blank( $_REQUEST['search']['product_column'][0] ) ? str_replace( '`', '', $_REQUEST['search']['product_column'][0] ) : '';
			$this->arr_search['product_column'][1]    = ! WCUtils::is_blank( $_REQUEST['search']['product_column'][1] ) ? str_replace( '`', '', $_REQUEST['search']['product_column'][1] ) : '';
			$this->arr_search['product_word'][0]      = ! WCUtils::is_blank( $_REQUEST['search']['product_word'][0] ) ? trim( $_REQUEST['search']['product_word'][0] ) : '';
			$this->arr_search['product_word'][1]      = ! WCUtils::is_blank( $_REQUEST['search']['product_word'][1] ) ? trim( $_REQUEST['search']['product_word'][1] ) : '';
			$this->arr_search['product_word_term'][0] = isset( $_REQUEST['search']['product_word_term'][0] ) ? $_REQUEST['search']['product_word_term'][0] : 'contain';
			$this->arr_search['product_word_term'][1] = isset( $_REQUEST['search']['product_word_term'][1] ) ? $_REQUEST['search']['product_word_term'][1] : 'contain';
			$this->arr_search['option_word'][0]       = ( isset( $_REQUEST['search']['option_word'][0] ) && ! WCUtils::is_blank( $_REQUEST['search']['option_word'][0] ) ) ? trim( $_REQUEST['search']['option_word'][0] ) : '';
			$this->arr_search['option_word'][1]       = ( isset( $_REQUEST['search']['option_word'][1] ) && ! WCUtils::is_blank( $_REQUEST['search']['option_word'][1] ) ) ? trim( $_REQUEST['search']['option_word'][1] ) : '';
			if ( WCUtils::is_blank( $_REQUEST['search']['product_column'][0]) ) {
				$this->arr_search['product_column'][1]    = '';
				$this->arr_search['product_word'][0]      = '';
				$this->arr_search['product_word'][1]      = '';
				$this->arr_search['product_word_term'][0] = 'contain';
				$this->arr_search['product_word_term'][1] = 'contain';
				$this->arr_search['option_word'][0]       = '';
				$this->arr_search['option_word'][1]       = '';
			}
			$this->arr_search['product_term'] = $_REQUEST['search']['product_term'];
			$this->currentPage                = 1;
			$this->sortColumn                 = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs                = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames            = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->totalRow                   = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->placeholder_escape         = $wpdb->placeholder_escape();

		} elseif ( isset( $_REQUEST['searchOut'] ) ) {

			$this->action                             = 'searchOut';
			$this->arr_search['column']               = '';
			$this->arr_search['word']                 = '';
			$this->arr_search['order_column'][0]      = '';
			$this->arr_search['order_column'][1]      = '';
			$this->arr_search['order_word'][0]        = '';
			$this->arr_search['order_word'][1]        = '';
			$this->arr_search['order_word_term'][0]   = 'contain';
			$this->arr_search['order_word_term'][1]   = 'contain';
			$this->arr_search['order_term']           = 'AND';
			$this->arr_search['product_column'][0]    = '';
			$this->arr_search['product_column'][1]    = '';
			$this->arr_search['product_word'][0]      = '';
			$this->arr_search['product_word'][1]      = '';
			$this->arr_search['product_word_term'][0] = 'contain';
			$this->arr_search['product_word_term'][1] = 'contain';
			$this->arr_search['option_word'][0]       = '';
			$this->arr_search['option_word'][1]       = '';
			$this->arr_search['product_term']         = 'AND';
			$this->currentPage                        = 1;
			$this->sortColumn                         = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs                        = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames                    = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->totalRow                           = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->placeholder_escape                 = '';

		} elseif ( isset( $_REQUEST['collective'] ) ) {

			$this->action             = 'collective_' . str_replace( ',', '', $_POST['allchange']['column'] );
			$this->currentPage        = ( isset( $this->data_cookie['currentPage'] ) ) ? $this->data_cookie['currentPage'] : $this->currentPage;
			$this->sortColumn         = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs        = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames    = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->searchWhere        = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : $this->searchWhere;
			$this->searchHaving       = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : $this->searchHaving;
			$this->arr_search         = ( isset( $this->data_cookie['arr_search'] ) ) ? $this->data_cookie['arr_search'] : $this->arr_search;
			$this->totalRow           = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->selectedRow        = ( isset( $this->data_cookie['selectedRow'] ) ) ? $this->data_cookie['selectedRow'] : $this->selectedRow;
			$this->placeholder_escape = ( isset( $this->data_cookie['placeholder_escape'] ) ) ? $this->data_cookie['placeholder_escape'] : $this->placeholder_escape;

		} elseif ( isset( $_REQUEST['refresh'] ) ) {

			$this->action             = 'refresh';
			$this->currentPage        = ( isset( $this->data_cookie['currentPage'] ) ) ? $this->data_cookie['currentPage'] : $this->currentPage;
			$this->sortColumn         = ( isset( $this->data_cookie['sortColumn'] ) ) ? $this->data_cookie['sortColumn'] : $this->sortColumn;
			$this->sortSwitchs        = ( isset( $this->data_cookie['sortSwitchs'] ) ) ? $this->data_cookie['sortSwitchs'] : $this->sortSwitchs;
			$this->userHeaderNames    = ( isset( $this->data_cookie['userHeaderNames'] ) ) ? $this->data_cookie['userHeaderNames'] : $this->userHeaderNames;
			$this->searchWhere        = ( isset( $this->data_cookie['searchWhere'] ) ) ? $this->data_cookie['searchWhere'] : $this->searchWhere;
			$this->searchHaving       = ( isset( $this->data_cookie['searchHaving'] ) ) ? $this->data_cookie['searchHaving'] : $this->searchHaving;
			$this->arr_search         = ( isset( $this->data_cookie['arr_search'] ) ) ? $this->data_cookie['arr_search'] : $this->arr_search;
			$this->totalRow           = ( isset( $this->data_cookie['totalRow'] ) ) ? $this->data_cookie['totalRow'] : $this->totalRow;
			$this->selectedRow        = ( isset( $this->data_cookie['selectedRow'] ) ) ? $this->data_cookie['selectedRow'] : $this->selectedRow;
			$this->placeholder_escape = '';

		} else {
			$this->action             = 'default';
			$this->placeholder_escape = '';
		}
	}

	/**
	 * Get Rows.
	 *
	 * @return array
	 */
	public function GetRows() {
		global $wpdb;

		$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';
		$order_table             = $wpdb->prefix . 'usces_order';
		$order_meta_table        = $wpdb->prefix . 'usces_order_meta';
		$ordercart_table         = $wpdb->prefix . 'usces_ordercart';
		$ordercart_meta_table    = $wpdb->prefix . 'usces_ordercart_meta';
		$member_table            = usces_get_tablename( 'usces_member' );
		$member_meta_table       = usces_get_tablename( 'usces_member_meta' );

		$where  = $this->GetWhere();
		$having = $this->GetHaving();

		$csod  = '';
		$join  = '';
		$join .= "INNER JOIN {$member_table} AS `mem` ON con_member_id = mem.ID ";
		$join .= "INNER JOIN {$order_table} AS `ord` ON con_order_id = ord.ID ";
		$join .= "LEFT JOIN {$member_meta_table} AS `mm` ON con_member_id = mm.member_id AND mm.meta_key = 'limitofcard' ";
		$join .= "LEFT JOIN {$order_meta_table} AS `om` ON con_order_id = om.order_id AND om.meta_key = 'dec_order_id' ";
		if ( $where ) {
			$join .= " LEFT JOIN {$ordercart_table} AS `cart` ON con_order_id = cart.order_id ";
			$csod .= ', cart.item_code, cart.item_name, cart.sku_code, cart.sku_name ';
			$join .= " LEFT JOIN {$ordercart_meta_table} AS `itemopt` ON cart.cart_id = itemopt.cart_id AND itemopt.meta_type = 'option' ";
			$csod .= ', itemopt.meta_key, itemopt.meta_value ';
		}
		$join = apply_filters( 'dlseller_filter_continue_memberlist_sql_jointable', $join, $this );

		$group  = ' GROUP BY `ID` ';
		$switch = ( 'ASC' === $this->sortSwitchs[ $this->sortColumn ] ) ? 'ASC' : 'DESC';
		$order  = ' ORDER BY `' . esc_sql( $this->sortColumn ) . '` ' . $switch;
		$query  = "SELECT 
			`con_id` AS `ID`, 
			`con_order_id` AS `order_id`, 
			om.meta_value AS `deco_id`, 
			`con_member_id` AS `mem_id`, 
			mem.mem_email AS `email`, 
			mem.mem_name1 AS `name1`, 
			mem.mem_name2 AS `name2`, 
			mem.mem_name3 AS `name3`, 
			mem.mem_name4 AS `name4`, 
			IFNULL(mm.meta_value, '') AS `limitofcard`, 
			`con_price` AS `price`, 
			`con_acting` AS `acting`, 
			ord.order_payment_name AS `payment_name`, 
			DATE_FORMAT(ord.order_date, '%Y-%m-%d') AS `orderdate`, 
			DATE_FORMAT(con_startdate, '%Y-%m-%d') AS `startdate`, 
			DATE_FORMAT(con_next_contracting, '%Y-%m-%d') AS `contractedday`, 
			DATE_FORMAT(con_next_charging, '%Y-%m-%d') AS `chargedday`, 
			`con_status` AS `status`, 
			`con_condition` AS `condition` 
			{$csod} 
			FROM {$this->table} ";
		$query  = apply_filters( 'dlseller_filter_continue_memberlist_sql_select', $query, $csod, $this );
		$query .= $join . $where . $group . $having . $order;
		if ( $this->placeholder_escape ) {
			add_filter( 'query', array( $this, 'remove_ph' ) );
		}
		$rows              = $wpdb->get_results( $query, ARRAY_A );
		$this->selectedRow = ( $rows && is_array( $rows ) ) ? count( $rows ) : 0;
		if ( 'on' === $this->pageLimit ) {
			$this->rows           = array_slice( $rows, $this->startRow, $this->maxRow );
			$this->currentPageIds = array();
			foreach ( $this->rows as $row ) {
				$this->currentPageIds[] = $row['ID'];
			}
		} else {
			$this->rows = $rows;
		}

		return $this->rows;
	}

	/**
	 * Placeholder clear.
	 *
	 * @param  string $query Query.
	 * @return string
	 */
	public function remove_ph( $query ) {
		return str_replace( $this->placeholder_escape, '%', $query );
	}

	/**
	 * Set Total Rows.
	 */
	public function SetTotalRow() {
		global $wpdb;

		$member_table   = usces_get_tablename( 'usces_member' );
		$order_table    = $wpdb->prefix . 'usces_order';
		$query          = "SELECT COUNT(con_id) AS `ct` FROM {$this->table} 
			INNER JOIN {$member_table} AS `mem` ON `con_member_id` = mem.ID 
			INNER JOIN {$order_table} AS `ord` ON `con_order_id` = ord.ID " . apply_filters( 'dlseller_filter_continue_memberlist_sql_where', '', $this );
		$query          = apply_filters( 'dlseller_filter_continue_memberlist_set_total_row', $query, $this );
		$res            = $wpdb->get_var( $query );
		$this->totalRow = $res;
	}

	/**
	 * Having Condition.
	 *
	 * @return string
	 */
	public function GetHaving() {
		$query = '';
		if ( ! WCUtils::is_blank( $this->searchHaving ) ) {
			$query .= ' HAVING ' . $this->searchHaving;
		}
		$query = apply_filters( 'dlseller_filter_continue_memberlist_sql_having', $query, $this );
		return $query;
	}

	/**
	 * Where Condition.
	 *
	 * @return string
	 */
	public function GetWhere() {
		$query = '';
		if ( ! WCUtils::is_blank( $this->searchWhere ) ) {
			$query .= ' WHERE ' . $this->searchWhere;
		}
		$query = apply_filters( 'dlseller_filter_continue_memberlist_sql_where', $query, $this );
		return $query;
	}

	/**
	 * Search.
	 */
	public function SearchIn() {
		global $wpdb;

		$this->searchWhere  = '';
		$this->searchHaving = '';

		if ( ! empty( $this->arr_search['order_column'][0] ) && ! WCUtils::is_blank( $this->arr_search['order_word'][0] ) ) {
			switch ( $this->arr_search['order_word_term'][0] ) {
				case 'notcontain':
					$wordterm0 = ' NOT LIKE %s';
					$word0     = '%' . $this->arr_search['order_word'][0] . '%';
					break;
				case 'equal':
					$wordterm0 = ' = %s';
					$word0     = $this->arr_search['order_word'][0];
					break;
				case 'morethan':
					$wordterm0 = ' > %d';
					$word0     = $this->arr_search['order_word'][0];
					break;
				case 'lessthan':
					$wordterm0 = ' < %d';
					$word0     = $this->arr_search['order_word'][0];
					break;
				case 'contain':
				default:
					$wordterm0 = ' LIKE %s';
					$word0     = '%' . $this->arr_search['order_word'][0] . '%';
					break;
			}
			switch ( $this->arr_search['order_word_term'][1] ) {
				case 'notcontain':
					$wordterm1 = ' NOT LIKE %s';
					$word1     = '%' . $this->arr_search['order_word'][1] . '%';
					break;
				case 'equal':
					$wordterm1 = ' = %s';
					$word1     = $this->arr_search['order_word'][1];
					break;
				case 'morethan':
					$wordterm1 = ' > %d';
					$word1     = $this->arr_search['order_word'][1];
					break;
				case 'lessthan':
					$wordterm1 = ' < %d';
					$word1     = $this->arr_search['order_word'][1];
					break;
				case 'contain':
				default:
					$wordterm1 = ' LIKE %s';
					$word1     = '%' . $this->arr_search['order_word'][1] . '%';
					break;
			}
			$this->searchHaving .= ' ( ';
			$this->searchHaving .= $wpdb->prepare( '`' . esc_sql( $this->arr_search['order_column'][0] ) . '`' . $wordterm0, $word0 );
			if ( ! empty( $this->arr_search['order_column'][1] ) && ! WCUtils::is_blank( $this->arr_search['order_word'][1] ) ) {
				$this->searchHaving .= ' ' . $this->arr_search['order_term'] . ' ';
				$this->searchHaving .= $wpdb->prepare( '`' . esc_sql( $this->arr_search['order_column'][1] ) . '`' . $wordterm1, $word1 );
			}
			$this->searchHaving .= ' ) ';
		}

		if ( ! empty( $this->arr_search['product_column'][0] ) && ! WCUtils::is_blank( $this->arr_search['product_word'][0] ) ) {

			switch ( $this->arr_search['product_word_term'][0] ) {
				case 'notcontain':
					$prowordterm0 = ' NOT LIKE %s';
					$proword0     = '%' . $this->arr_search['product_word'][0] . '%';
					break;
				case 'equal':
					$prowordterm0 = ' = %s';
					$proword0     = $this->arr_search['product_word'][0];
					break;
				case 'morethan':
					$prowordterm0 = ' > %d';
					$proword0     = $this->arr_search['product_word'][0];
					break;
				case 'lessthan':
					$prowordterm0 = ' < %d';
					$proword0     = $this->arr_search['product_word'][0];
					break;
				case 'contain':
				default:
					$prowordterm0 = ' LIKE %s';
					$proword0     = '%' . $this->arr_search['product_word'][0] . '%';
					break;
			}
			switch ( $this->arr_search['product_word_term'][1] ) {
				case 'notcontain':
					$prowordterm1 = ' NOT LIKE %s';
					$proword1     = '%' . $this->arr_search['product_word'][1] . '%';
					break;
				case 'equal':
					$prowordterm1 = ' = %s';
					$proword1     = $this->arr_search['product_word'][1];
					break;
				case 'morethan':
					$prowordterm1 = ' > %d';
					$proword1     = $this->arr_search['product_word'][1];
					break;
				case 'lessthan':
					$prowordterm1 = ' < %d';
					$proword1     = $this->arr_search['product_word'][1];
					break;
				case 'contain':
				default:
					$prowordterm1 = ' LIKE %s';
					$proword1     = '%' . $this->arr_search['product_word'][1] . '%';
					break;
			}
			$this->searchWhere .= ' ( ';
			if ( 'item_option' === $this->arr_search['product_column'][0] ) {
				$this->searchWhere .= $wpdb->prepare( '( itemopt.meta_key LIKE %s AND itemopt.meta_value LIKE %s )', '%' . $this->arr_search['product_word'][0] . '%', '%' . $this->arr_search['option_word'][0] . '%' );
			} else {
				$this->searchWhere .= $wpdb->prepare( esc_sql( $this->arr_search['product_column'][0] ) . $prowordterm0, $proword0 );
			}
			if ( ! empty( $this->arr_search['product_column'][1] ) && ! WCUtils::is_blank( $this->arr_search['product_word'][1] ) ) {
				$this->searchWhere .= ' '. $this->arr_search['product_term'] . ' ';
				if ( 'item_option' === $this->arr_search['product_column'][1] ) {
					$this->searchWhere .= $wpdb->prepare( '( itemopt.meta_key LIKE %s AND itemopt.meta_value LIKE %s )', '%' . $this->arr_search['product_word'][1] . '%', '%' . $this->arr_search['option_word'][1] . '%' );
				} else {
					$this->searchWhere .= $wpdb->prepare( esc_sql( $this->arr_search['product_column'][1] ) . $prowordterm1, $proword1 );
				}
			}
			$this->searchWhere .= ' ) ';
		}
	}

	/**
	 * Search clear.
	 */
	public function SearchOut() {
		$this->searchWhere  = '';
		$this->searchHaving = '';
	}

	/**
	 * Set Navigation.
	 */
	public function SetNavi() {
		$this->lastPage     = (int) ceil( $this->selectedRow / $this->maxRow );
		$this->previousPage = ( ( $this->currentPage - 1 ) === 0 ) ? 1 : $this->currentPage - 1;
		$this->nextPage     = ( ( $this->currentPage + 1 ) > $this->lastPage ) ? $this->lastPage : $this->currentPage + 1;
		$box                = array();

		for ( $i = 0; $i < $this->naviMaxButton; $i++ ) {
			if ( $i > ( $this->lastPage - 1 ) ) {
				break;
			}
			if ( $this->lastPage <= $this->naviMaxButton ) {
				$box[] = $i + 1;
			} else {
				if ( $this->currentPage <= 6 ) {
					$label = $i + 1;
					$box[] = $label;
				} else {
					$label = $i + 1 + $this->currentPage - 6;
					$box[] = $label;
					if ( $label === $this->lastPage ) {
						break;
					}
				}
			}
		}

		$html  = '';
		$html .= '<ul class="clearfix">';
		$html .= '<li class="rowsnum">' . $this->selectedRow . ' / ' . $this->totalRow . ' ' . __( 'cases', 'usces' ) . '</li>';
		if ( ( 1 === $this->currentPage ) || ( 0 === (int) $this->selectedRow ) ) {
			$html .= '<li class="navigationStr">first&lt;&lt;</li>';
			$html .= '<li class="navigationStr">prev&lt;</li>';
		} else {
			$html .= '<li class="navigationStr"><a href="' . get_option( 'siteurl' ) . '/wp-admin/admin.php?page=usces_continue&changePage=1">first&lt;&lt;</a></li>';
			$html .= '<li class="navigationStr"><a href="' . get_option( 'siteurl' ) . '/wp-admin/admin.php?page=usces_continue&changePage=' . $this->previousPage . '">prev&lt;</a></li>';
		}
		if ( 0 < $this->selectedRow ) {
			$box_count = count( $box );
			for ( $i = 0; $i < $box_count; $i++ ) {
				if ( (int) $box[ $i ] === (int) $this->currentPage ) {
					$html .= '<li class="navigationButtonSelected"><span>' . $box[ $i ] . '</span></li>';
				} else {
					$html .= '<li class="navigationButton"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_continue&changePage=' . $box[ $i ] . '">' . $box[ $i ] . '</a></li>';
				}
			}
		}
		if ( ( $this->currentPage === $this->lastPage ) || ( 0 === (int) $this->selectedRow ) ) {
			$html .= '<li class="navigationStr">&gt;next</li>';
			$html .= '<li class="navigationStr">&gt;&gt;last</li>';
		} else {
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_continue&changePage=' . $this->nextPage . '">&gt;next</a></li>';
			$html .= '<li class="navigationStr"><a href="' . site_url() . '/wp-admin/admin.php?page=usces_continue&changePage=' . $this->lastPage . '">&gt;&gt;last</a></li>';
		}
		$html .= '</ul>';

		$this->dataTableNavigation = $html;
	}

	/**
	 * Get Cookie.
	 */
	public function getCookie() {
		$this->data_cookie = ( isset( $_COOKIE[ $this->table ] ) ) ? json_decode( str_replace( "\'", "'", str_replace( '\"', '"', $_COOKIE[ $this->table] ) ), true ) : array();
	}

	/**
	 * Set Headers.
	 */
	public function SetHeaders() {
		foreach ( $this->columns as $key => $value ) {
			if ( $key === $this->sortColumn ) {
				if ( 'ASC' === $this->sortSwitchs[ $key ] ) {
					$str    = __( '[ASC]', 'usces' );
					$switch = 'DESC';
				} else {
					$str    = __( '[DESC]', 'usces' );
					$switch = 'ASC';
				}
				$this->headers[ $key ] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_continue&changeSort=' . $key . '&switch=' . $switch . '"><span class="sortcolumn">' . $value . ' ' . $str . '</span></a>';
			} else {
				$switch                = ( isset( $this->sortSwitchs[ $key ] ) ) ? $this->sortSwitchs[ $key ] : 'DESC';
				$this->headers[ $key ] = '<a href="' . site_url() . '/wp-admin/admin.php?page=usces_continue&changeSort=' . $key . '&switch=' . $switch . '"><span>' . $value . '</span></a>';
			}
		}
	}

	/**
	 * Get Search.
	 *
	 * @return string
	 */
	public function GetSearchs() {
		return $this->arr_search;
	}

	/**
	 * Get Headers.
	 *
	 * @return string
	 */
	public function GetListheaders() {
		return $this->headers;
	}

	/**
	 * Get Navigation.
	 *
	 * @return string
	 */
	public function GetDataTableNavigation() {
		return $this->dataTableNavigation;
	}

	/**
	 * Set Action Status and Action Message.
	 *
	 * @param string $action_status  Action status.
	 * @param string $action_message Action message.
	 */
	public function set_action_status( $action_status, $action_message ) {
		$this->action_status  = $action_status;
		$this->action_message = $action_message;
	}

	/**
	 * Get Action Status.
	 *
	 * @return string
	 */
	public function get_action_status() {
		return $this->action_status;
	}

	/**
	 * Get Action Status.
	 *
	 * @return string
	 */
	public function get_action_message() {
		return $this->action_message;
	}
}
