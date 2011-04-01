<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class DiffusionHistoryController extends DiffusionController {

  public function processRequest() {
    $drequest = $this->diffusionRequest;
    $request = $this->getRequest();

    $page_size = $request->getInt('pagesize', 100);
    $offset = $request->getInt('page', 0);

    $history_query = DiffusionHistoryQuery::newFromDiffusionRequest(
      $drequest);
    $history_query->setOffset($offset);
    $history_query->setLimit($page_size + 1);
    $history = $history_query->loadHistory();

    $pager = new AphrontPagerView();
    $pager->setPageSize($page_size);
    $pager->setOffset($offset);
    if (count($history) == $page_size + 1) {
      array_pop($history);
      $pager->setHasMorePages(true);
    } else {
      $pager->setHasMorePages(false);
    }
    $pager->setURI($request->getRequestURI(), 'page');

    $content = array();

    $content[] = $this->buildCrumbs(
      array(
        'branch' => true,
        'path'   => true,
        'view'   => 'history',
      ));

    $history_table = new DiffusionHistoryTableView();
    $history_table->setDiffusionRequest($drequest);
    $history_table->setHistory($history);

    $history_panel = new AphrontPanelView();
    $history_panel->appendChild($history_table);
    $history_panel->appendChild($pager);

    $content[] = $history_panel;

    // TODO: Sometimes we do have a change view, we need to look at the most
    // recent history entry to figure it out.

    $nav = $this->buildSideNav('history', false);
    $nav->appendChild($content);

    return $this->buildStandardPageResponse(
      $nav,
      array(
        'title' => 'history',
      ));
  }

}
