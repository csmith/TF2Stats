<?PHP

 define('TITLE', 'About');
 require('inc/header.php');

?>
 <h2>About TF2 Stats</h2>
 <div class="left">
  <h3>About</h3>
  <p>
   TF2 Stats is a web application which creates statistics about
   <a href="http://www.teamfortress.com/">Team Fortress 2</a> servers and
   their players. It is being developed by
   <a href="http://chris.smith.name/">Chris Smith</a>.
  </p>
  <p>
   TF2 Stats works by analysing the log files produced by TF2 servers. It
   records all events that take place (such as kills, team changes, captures,
   and many more), and then analyses those events to produce interesting
   statistics.
  </p>
  <h3>Get in touch</h3>
  <p>
   If you have spotted a bug, want to request a feature, or run a server and want to
   be part of TF2 Stats, please feel free to get in touch. TF2 Stats is developed
   and operated by Chris Smith, whose contact details are available on his
   <a href="http://chris.smith.name/">personal website</a>.
  </p>
  <h3>Frequently Asked Questions</h3>
  <dl>
   <dt>Q. What happens if I change name?</dt>
   <dd>A. TF2 Stats tracks players by their Steam IDs, rather than by their
          aliases, so you can change your nickname as often as you like
          and we'll keep track of you.</dd>
   <dt>Q. How is my score calculated?</dt>
   <dd>A. Your score starts out at 1,000, and increases or decreases with
          each event that you are involved in. Most changes come from kills,
          where the killer takes a certain amount of points from their victim,
          based on the difference between their scores, their classes, and
          the weapons used.
   </dd>
  </dl>
 </div>
 <div class="right">
  <h3>Recent changes</h3>
  <ul>
   <li>Improved the <a href="weapons.php">weapons</a> page to include more information and a chart</li>
   <li>Added a 'Terrific Taunter' award</li>
   <li>Added a very basic <a href="groups.php">groups</a> page</li>
   <li>Created an <a href="about.php">about</a> page which gives a brief overview of TF2 Stats and shows a changelog</li>
   <li>Updated server names for Spec-Ops servers</li>
   <li>Enabled automatic clan detection in the importer</li>
   <li>Fixed issue with Firefox causing the menu to wrap when selecting or clicking links</li>
   <li>Added a new page for groups (clans) — <a href="group.php?group=160">see an example here</a></li>
   <li>Group affiliations are now displayed on player pages (<a href="player.php?id=3778">example</a>)</li>
   <li>Implemented the 'award winners' panel on the <a href="players.php">players</a> page</li>
   <li>Added missing weapon images for heavy unlockables and some pyro deflection types</li>
   <li>Created the <a href="weapons.php">weapons</a> page, which shows the number of kills by every known weapon</li>
   <li>Added logs from two new servers operated by <a href="http://www.special-ops.us/">[Spec-Ops]</a></li>
  </ul>
 </div>
<?PHP

 require('inc/footer.php');

?>
