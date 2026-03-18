<?php

use MediaWiki\SpecialPage\SpecialPage;

/**
 * SpecialPage file for GlobalStats
 * @ingroup Extensions
 */
class GlobalStats extends SpecialPage {
	public function __construct() {
		parent::__construct( 'GlobalStats' );
	}

	public function execute( $par ) {
		global $wgSitename;

		$this->setHeaders();
		$request = $this->getRequest();
		$out = $this->getOutput();

		$out->addHTML( "
			<style>
				.mw-globalstats-table {
					border: 1px #aaa solid;
					border-collapse: collapse;
				}
				.mw-globalstats-table th,
				.mw-globalstats-table td {
					border: 1px #aaa solid;
					padding: 2px;
					text-align: left;
				}
				.mw-globalstats-form table,
				.mw-globalstats-form td,
				.mw-globalstats-form th {
					border-width: 0;
				}
				.mw-globalstats-checkbox {
					width: 130px;
				}
			</style>
		" );

		$fpath = __DIR__ . '/data/' . $wgSitename . '.csv';
		if ( !is_readable( $fpath ) ) {
			$out->addWikiTextAsInterface( "''" . $this->msg( 'globalstats-error-missingfile' )->escaped() . "''" );
			return;
		}

		$rows = @file( $fpath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( !$rows || count( $rows ) < 2 ) {
			$out->addWikiTextAsInterface( "''" . $this->msg( 'globalstats-error-emptyfile' )->escaped() . "''" );
			return;
		}

		$dataRows = array_slice( $rows, 1 );
		$parsedRows = [];
		$days = [];
		$todayRow = null;
		$today = date( 'Y-m-d' );

		foreach ( $dataRows as $row ) {
			$cols = explode( ';', rtrim( $row ) );
			if ( !isset( $cols[0] ) || strlen( $cols[0] ) < 10 ) {
				continue;
			}
			$date = substr( $cols[0], 0, 10 );
			$cols[0] = $date;
			$parsedRows[] = $cols;
			$days[] = $date;
			if ( $date === $today ) {
				$todayRow = $cols;
			}
		}

		if ( !$parsedRows ) {
			$out->addWikiTextAsInterface( "''" . $this->msg( 'globalstats-error-emptyfile' )->escaped() . "''" );
			return;
		}

		if ( $todayRow === null ) {
			$todayRow = [ $today, '?', '?', '?', '?', '?', '?', '?' ];
		}

		$defaultFrom = count( $days ) < 31 ? $days[0] : $days[count( $days ) - 31];
		$defaultTo = $days[count( $days ) - 1];

		$from = $request->getText( 'from', $defaultFrom );
		if ( !in_array( $from, $days, true ) ) {
			$from = $defaultFrom;
		}

		$to = $request->getText( 'to', $defaultTo );
		if ( !in_array( $to, $days, true ) || $to < $from ) {
			$to = $defaultTo;
		}

		$selectedCheckboxes = $request->getArray( 'chb', [] );
		$chb = [];
		for ( $i = 0; $i < 7; $i++ ) {
			$chb[$i] = $selectedCheckboxes === [] ? 1 : isset( $selectedCheckboxes[$i] );
		}

		$csvExport = $request->getCheck( 'CSVexport' );

		$out->addWikiTextAsInterface( '<h2>' . $this->msg( 'gs_todaystat' )->escaped() . '</h2>' );
		$out->addHTML( '<table class="mw-globalstats-table">' );
		$out->addHTML( '<tr><th style="width:40px">date</th><td>' . $this->msg( 'gs_date' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[0] ) . '</td></tr>' );
		$out->addHTML( '<tr><th>total</th><td>' . $this->msg( 'gs_total' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[1] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>good</th><td>' . $this->msg( 'gs_good' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[2] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>edits</th><td>' . $this->msg( 'gs_edits' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[3] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>users</th><td>' . $this->msg( 'gs_users' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[4] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>admins</th><td>' . $this->msg( 'gs_admins' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[5] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>images</th><td>' . $this->msg( 'gs_images' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[6] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '<tr><th>activeusers</th><td>' . $this->msg( 'gs_activeusers' )->escaped() . '</td><td>' . htmlspecialchars( $todayRow[7] ?? '?' ) . '</td></tr>' );
		$out->addHTML( '</table><br><br>' );

		$out->addWikiTextAsInterface( '<h2>' . $this->msg( 'gs_completestat' )->escaped() . '</h2>' );
		$out->addHTML( '<form id="form" name="form" class="mw-globalstats-form" action="" method="post">' );
		$out->addHTML( '<fieldset><legend>' . $this->msg( 'gs_settings' )->escaped() . '</legend>' );
		$out->addHTML( '<table>' );
		$out->addHTML( '<tr>' );
		$out->addHTML( '<th>' . $this->msg( 'gs_from' )->escaped() . ':</th><td><select name="from">' );
		foreach ( $days as $day ) {
			$out->addHTML( '<option value="' . htmlspecialchars( $day ) . '"' . ( $day === $from ? ' selected="selected"' : '' ) . '>' . htmlspecialchars( $day ) . '</option>' );
		}
		$out->addHTML( '</select></td><td></td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[0]"' . ( $chb[0] ? ' checked="checked"' : '' ) . '> total</td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[1]"' . ( $chb[1] ? ' checked="checked"' : '' ) . '> good</td>' );
		$out->addHTML( '</tr>' );
		$out->addHTML( '<tr>' );
		$out->addHTML( '<th>' . $this->msg( 'gs_to' )->escaped() . ':</th><td><select name="to">' );
		foreach ( $days as $day ) {
			$out->addHTML( '<option value="' . htmlspecialchars( $day ) . '"' . ( $day === $to ? ' selected="selected"' : '' ) . '>' . htmlspecialchars( $day ) . '</option>' );
		}
		$out->addHTML( '</select></td><td></td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[3]"' . ( $chb[3] ? ' checked="checked"' : '' ) . '> users</td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[4]"' . ( $chb[4] ? ' checked="checked"' : '' ) . '> admins</td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[5]"' . ( $chb[5] ? ' checked="checked"' : '' ) . '> images</td>' );
		$out->addHTML( '</tr>' );
		$out->addHTML( '<tr><td colspan="3"></td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[6]"' . ( $chb[6] ? ' checked="checked"' : '' ) . '> activeusers</td>' );
		$out->addHTML( '<td class="mw-globalstats-checkbox"><input type="checkbox" name="chb[2]"' . ( $chb[2] ? ' checked="checked"' : '' ) . '> edits</td>' );
		$out->addHTML( '</tr>' );
		$out->addHTML( '<tr><td colspan="7" style="height:30px;vertical-align:bottom;">' );
		$out->addHTML( '<input type="submit" name="update" value="' . $this->msg( 'gs_refresh' )->escaped() . '">&nbsp;' );
		$out->addHTML( '<input type="submit" name="CSVexport" value="' . $this->msg( 'gs_export' )->escaped() . '">' );
		$out->addHTML( '</td></tr></table></fieldset></form><br><br>' );

		$headerMap = [
			0 => 'total',
			1 => 'good',
			2 => 'edits',
			3 => 'users',
			4 => 'admins',
			5 => 'images',
			6 => 'activeusers',
		];

		$export = 'date';
		$out->addHTML( '<table class="mw-globalstats-table"><tr><th>date</th>' );
		foreach ( $headerMap as $index => $label ) {
			if ( $chb[$index] ) {
				$out->addHTML( '<th>' . htmlspecialchars( $label ) . '</th>' );
				$export .= ';' . $label;
			}
		}
		$export .= "\r\n";
		$out->addHTML( '</tr>' );

		foreach ( $parsedRows as $row ) {
			if ( $row[0] < $from || $row[0] > $to ) {
				continue;
			}
			$out->addHTML( '<tr><td>' . htmlspecialchars( $row[0] ) . '</td>' );
			$export .= $row[0];
			for ( $i = 1; $i <= 7; $i++ ) {
				if ( $chb[$i - 1] ) {
					$value = $row[$i] ?? '';
					$out->addHTML( '<td>' . htmlspecialchars( $value ) . '</td>' );
					$export .= ';' . $value;
				}
			}
			$export .= "\r\n";
			$out->addHTML( '</tr>' );
		}
		$out->addHTML( '</table>' );

		if ( $csvExport ) {
			$out->disable();
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="' . rawurlencode( $wgSitename ) . '.csv"' );
			echo $export;
			return;
		}
	}
}
