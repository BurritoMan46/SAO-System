<?php

namespace NetherTechnology\SAO;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;

use pocketmine\event\Listener;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\format\FullChunk;

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\Inventory;

use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

use pocketmine\block\Block;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerIntersectEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\eneity\EntityDespawnEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config; 
use pocketmine\utils\Utils;

use SAO\NPC\TradeNPC;
use SAO\NPC\NPC;
use SAO\NPC\ChatNPC;

use pocketmine\command\Command;
use pcoketmine\command\CommandSender;

class Main extends PluginBase implements Listener {
	private $config;
	
	
        public function onEnable() {
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	    @mkdir($this->getDataFolder()); 
        @mkdir($this->getDataFolder() . "\\players"); 
		@mkdir($this->getDataFolder() . "\\clan");
		$this->getLogger()->info("CoreSystem By @DakarOmar Loaded Successfully");
		$this->Listener = new EventListener($this);
		$this->pvpcfg = new Config($this->getDataFolder() . "pvpconfig.yml", Config::YAML, [
          "UnPVPmessage" => "You're not allowed to hurt players here.",
          "UnPVPWorld" => []
        ]);
		$this->getServer()->setConfigBool("pvp", true);
	}
	public function onBreak(BlockBreakEvent $event) {
	    $player = $event->getPlayer();
		if($player->isOp())
		{
		    $this->getLogger("$player place a block");
		}else{
		    $player->sendMessage("You are not op!");
			$event->setCancelled(true);
		}
	}
	public function onLobbyCrystal(PlayerIntersectEvent $event) {
		$crystal = $this->getPlayer()->getItemInHand()->getId();
		$config = $this->getGameConfig();
		$player = $event->getPlayer();
		if ($crystal == 322) {
			$level = $config->get("level");
			$player->setLevel($level);
		}
	}
	public function onChat(PlayerChatEvent $event){
		$player = $event->getPlayer()
		$config = $this->getPlayerConfig($player->getName());
		$logged = $config->get("logged");
		if($logged == false) {
			$event->setCancelled(true);
		}
	}
	public function onPlace(BrockPlaceEvent $event) {
	    $player = $event->getPlayer();
		if($player->isOp())
		{
		    $this->getLogger()->info("$player place a block");
	    }else{
		    $player->sendMessage("You are not op");
			$event->setCancelled(true);
		}
	}
	
	public function onLoginMovement(PlayerMoveEvent $event) {
		$config = $this->getPlayerConfig($name);
		$logged = $config->get("logged");
		$player = $event->getPlayer();
		$name = $player->getName();
		if($logged == "false") {
			$player->sendMessage("Please Login to Move!!");
			$event->setCancelled(true);
		}
	}
	
	public function onDeath(PlayerDeathEvent $event) {
	    $player = $event->getPlayer();
		$player->setBanned(true);
	}
	
	public function onQuit(PlayerQuitEvent $event) {
	    $player = $event->getPlayer();
	    $name = $player->getName();
	    $config = $this->getPlayerConfig($name);
	    $logged = $config->get("logged");
	    $config->set("logged", "false");
	}
	
	public function onJoin(PlayerJoinEvent $event) {
 	$player = $event->getPlayer();
    $id = $player->getName();
	$config = $this->getPlayerConfig($id);
	$logged = $config->get("logged");
	if ($logged == false) {
		$player->sendMessage("Please Login to move!!");
	}else{
		$player->sendMessage("Welcome come back to SAO Server");
	}
	$player->setNameTag("$id");
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		switch ($command->getName()) {
			case "Exp";
			$config = $this->getPlayerConfig($arg[1]);
			$logged = $config->get("logged");
			if ($sender instanceof Player) {
				if ($logged == true) {
					if($sender->isOp()) {
				        if(!empty ($arg[0])){
					        if(strtolower($arg[0]) == "setexp"){
					        	if(!empty ($arg[1])) {
					        		if(!empty ($arg[2])) {
					        			if($arg[2] > 0 and $arg[2] <= 100) {
					         				$config->set("level", $arg[2]);
				         					$config->save();
					        				$sender->sendMessage("Set player $arg[1] level set to $arg[2]");
					        				return true;
					        			} else {
					        				$sender->sendMessage("Level must be between 1 - 100");
							        		return true;
						        		}
					        		} else {
						        		$sender->sendMessage("ERROR");
				        			}
				        		} else {
					        		$sender->sendMessage("ERROR");
				        		}
			        		} 
			        		elseif(strtolower($arg[0]) == "checklevel") {
					        	if(!empty ($arg[1])){
							        $config = $this->getPlayerConfig($arg[1]);
	        						$level = $config->get("level");
			        				$sender->sendMessage("$arg[1] level is : $level");
					        	} else {
				        			$sender->sendMessage(ERROR);
				        		}
				        	}
				        	elseif(strtolower($arg[0]) == "checkexp") {
					        	if(!empty ($arg[1])) {
					        		$config = $this->getPlayerConfig($arg[1]);
					        		$exp = $config->get("exp");
						        	$sender->sendMessage("$arg[1] exp is : #exp");
				        		}else{
							        $sender->sendMessage("ERROR");
					        	}
				        	}
				        	elseif(strtolower($arg[0]) == "help"){
					        	$sender->sendMessage("===============[AVERSION EXP SYSTEM HELP]===============");
						        $sender->sendMessage("/Exp help - Check help");
						        $sender->sendMessage("/Exp checklevel <player> - Check Player level");
						        $sender->sendMessage("/Exp checkexp <player> - Check Player Exp");
						        $sender->sendMessage("/Exp setlevel <player> <level> - Set Player Levels");
					        }
				        }else{
					        $sender->sendMessage("ERROR");
			        	}
		        	}else{
			        	$sender->sendMessage("You do not have permission");
			        }
				}
			}
			case "signup";
			    if($sender instanceof Player) {
					$config = $this->getPlayerConfig($sender->getName());
					$day = $config->get("daily-money");
					$nmoney = $config->get("money");
					$dmoney = $config->get("dmoney");
					$gmoney = $nmoney + $dmoney + 1 - 1;
					if (date("Y-m-d", time()) != $day) {
						$config->set("money", $gmoney);
						$config->save();
						$sender->sendMessage("You have signed up Successfully, \n You have Got $dmoney coins");
						return true;
					} else {
						$sender->sendMessage("You have Already signed up!!!");
					}
				} else {
					$sender->sendMessage("You are not a player!");
				}
			case "Login";
			    if($sender instanceof Player) {
					$config = $this->getPlayerConfig($sender->getName());
					$password = $config->get("password");
					$registered = $config->get("registered");
					$logged = $config->get("logged");
					if($registered == true) {
						if($logged == false) {
							if(!empty($arg[0])) {
								if($arg[0] == $password) {
									$config->set("logged", true);
									$sender->sendMessage("You have logged in!!!");
								}else{
									$sender->sendMessage("Your password is incorrect!!!");
								}
							}else{
								$sender->sendMessage("Please Enter your password!!!");
							}
						}else{
							$sender->sendMessage("You have logged in!!!");
						}
					}else{
						$sender->sendMessage("Please Register!!!");
					}
				}else{
					$sender->sendMessage("Please runn this command in game");
				}
			case "Register";
			    if($sender instanceof Player) {
					$config = $this->getPlayerConfig($sender->getName());
					$registered = $config->get("registered");
					if($registered == false) {
						if(!empty($arg[0])) {
							$config->set("registered", true);
							$config->set("password", $arg[0]);
							$sender->sendMessage("You have registered!!!");
						}else{
							$sender->sendMessage("Please enter the passsword!!!");
						}
					}else{
						$sender->sendMessage("YOu have already registered!!!");
					}
				}else{
					$sender->sendMessage("Please run command in game!!!");
				}
			case "lobby"
			    if($sender instanceof Player) {
					$config = $this->getGameConfig();
					$lobby = $config->get("lobby");
					$pconfig = $this->getPlayerConfig($sender->getName());
					$logged = $pconfig->get("logged");
					if($logged == true) {
						$sender->setLevel($lobby);
					}else{
						$sender->sendmessage("Please log in first");
					}
				}else{
					$sender->sendMessage("Please run this command in game");
				}
		    case "SAO";
			$config == $this->getPlayerConfig($sender->getName());
			$logged == $config->get("logged");
			if($logged == true) {
				if(!empty ($arg[0])) {
					if(strtolower($arg[0]) == "economy") {
						if(strtolower($arg[1]) == "shop") {
							if($sender instanceof Player) {
							    $this->shop($sender, $arg[2], $arg[3]);
						    } else {
							    $sender->sendMessage(You are not a player);
						    }
					    }
						elseif(strtolower($arg[1]) == "pay") {
							if($sender instanceof Player) {
								if(!empty ($arg[1])) {
									if(!empty ($arg[2])) {
										$config = $this->getPlayerConfig($sender->getName());
										$bconfig = $this->getPlayerConfig($arg[2]);
										if($bconfig == null) {
											$sender->sendMessage("Player never connected");
										}else{
											$config->set("money", $config->get("money") - $arg[3]);
											$bconfig->set("money", $bonfig->get("money" + $arg[3]));
											$config->save();
											$bconfig->save();
											$sender->sendMessage("You have pay $arg[2] to $arg[3]");
										}
									}
								}
							}
						}
				    }
					elseif($arg[0] == "love") {
						if($sender instanceof Player) {
							if(!empty ($arg[1])) {
								if(strtolower($arg[1]) == "Mary") {
									$player = $this->getServer()->getPlayer($arg[2]);
									$sendor = $sender->getName();
									$config = $this->getPlayerConfig($sendor);
									$lover = $config->get("lover");
									$bconfig = $this->getPlayerConfig($arg[2]);
									$blover = $bconfig->get("lover");
									if($player == null) {
										$sender=>sendMessage("This player is offline!");
									} elseif(empty ($blover)) {
									    $sender->sendMessage("This player is married!!!");										
									} else {
										$sender->sendMessage("You have ask $arg[2] for mary successfully");
										$arg[2]->sendMessage("$sendor is asking for mary you! \n Please type /SAO love accept tp accept it!");
										$config->set("wlover", $arg[2]);
										$bconfig->set("wlover", $sendor);
									}
								}
							    elseif(strtolower($arg[1]) == "accept") {
									$sendor = $sender->getName();
									$config = $this->getPlayerConfig($sendor);
									$wlover = $config->get("wlover");
									$bconfig = $this->getPlayerConfig($lover);
									if(!empty ($wlover)) {
										$sender->sendMessage("You have Married #wlover successfully!");
										$this->getServer->broadMessage("$wlover has alredy married $sendor!!!");
										$config->set("lover", $wlover);
										$bconfig->set("lover", $sendor);
									} else {
										$sender->sendMessage("You dont have anyone asked for mary");
									}
								}
							}
						}
					}
					elseif(strtolower($arg[0]) == "team") {
						if(!empty ($arg[1])) {
							if(strtolower($arg[1]) == "team") {
								$sendor = $sender->getName();
								$config = $this->getPlayerConfig($sendor);
								$ateamm = $config->get("teamm");
								$awteam = $config->get("wteam");
								$teamm = $arg[2]
								$tmconfig = $this->getPlayerConfig($teamm);
								$bteamm = $tmconfig->get("teamm");
								$bwteam = $tmconfig->get("wteam");
								if(!empty ($ateamm)) {
									$sender->sendMessage("You have a teammate already");
								}
								elseif(!empty ($bteamm)) {
									$sender->sendMessage("$arg[2] have a teammate already");
								}
								elseif(!empty ($bwteam)) {
									$sender->sendMessage("There is one person asking for team to $arg[2],plz wait for next time");
								}
								elseif(!empty ($awteam)) {
									$sender->sendMessage("You are asking for team to another people");
								}else{
									$awteam->set($arg[2]);
									$bwteam->set($sendor);
									$sender->sendMessage("You have asked $arg[2] for team");
									$teamm->sendMessage("$sendor is asking you for team, plz type /SAO team accept to accept it");
								}
							}
							elseif(strtolower($arg[1]) == "accept") {
								$sendor = $sender->getName();
								$config = $this->getPlayerConfig($sendor);
								$ateam = $config->get("teamm");
								$awteam = $config->get("wteam");
								$teamm = $arg[2]
								$bconfig = $->Pconfig($team);
								$bteam = $bconfig->get("teamm");
								$bwteam = $bconfig("wteam");
								if(!empty ($awteam)) {
									if(!empty ($bwteam)) {
										if($awteam == ($arg[2])) {
										    $ateam->set("$arg[2]");
										    $bteam->set("$sender");
										    $sender->sendMessage("You have teamed with $arg[2]");
										    $teamm->sendMessage("YOu have teamed with $sender");
									    }else{
										    $sender->sendMessage("$arg[2] didn't ask you for teaming");
									    }
									}else{
										$sender->sendMessage("$arg[2] didn't ask you for teaming");
									}
								}else{
									$sender->sendMessage("No one is asking you for team");
								}
							}
							elseif(strtolower($arg[1]) == "deneny") {
								$sendor = $sender->getName();
								$config = $this->getPlayerConfig($sendor);
								$awteam = $config->get("wteam");
								$teamm = $arg[2]
								$bconfig = $this->getPlayerConfig($teamm);
								$bwteam = $bconfig->get("wteam");
								if(!empty ($awteam)) {
									if(!empty ($bwteam)) {
										if(strtolower($awteam) == strtolower($arg[2])) {
											if($bwteam == $sendor) {
												$awteam->set("");
												$bwteam0>set("");
												$sender->sendMessage("YOu have denyed $arg[2]'s teaming asking");
												$teamm->sendMessage("$sender have deny your asking");
											}else{
												$sender->sendMessage("$teamm didnt ask you for teaming")
											}
										}else{
											$sender->sendMessage("$teamm didt ask you for teaming");
										}
									}else{
										$sender->sendMessage("$teamm is adking you for teaming");
									}
								}else{
									$sender->sendMessage("Noone is asking you for teaming");
								}
							}elseif(strtolower($arg[1]) == "demiss") {
								$sendor = $sender->getName();
								$config = $this->getPlayerConfig($name);
								$bteamm = $config->get("teamm");
								$bconfig = $this->getPlayerConfig($teamm);
								$config->set("teamm", "");
								$bconfig->set("teamm". "");
								$sender->sendMessage("You have demiss the team!");
							}
							elseif(strtolower($arg[1]) == "help") {
								$sender->sendMessage("=======================[AVERSION TEAM HELP]=========================");
								$sender->sendMessage("/SAO team team <player = Team with player>");
								$sender->sendMessage("/SAO team deneny <player> = Deneny the inventation of player");
								$sender->sendMessage("/SAO team accept <player> = Accept the teaming of player");
							}
						}
					}
					elseif(strtolower($arg[0]) == "clan") {
						$sendor = $sender->getName();
						$pconfig = $this->getPlayerConfig($sendor);
						if(!empty ($arg[1])) {
							if(strtolower($arg[1]) == "create"){
								if(!empty ($arg[2])) {
									$owner = $sendor;
									$config = $this->getClanConig($arg[2]);
									$config->set("name", $arg[2]);
									$config->set("owner", $owner);
									$config->set("exist", "true");
									$money = $pconfig->get("money");
									$cm = 10000;
									$pconfig->set("money", ($momey - $cm));
									$sender->sendMessage("You have successfully created a clan");
								}
							}
							elseif(strtolower($arg[1]) == "quit") {
								$clan = $pconfig->get("clan");
								$config = $this->getClanConfig($clan);
								$owner = $config->get("owner");
								if(empty $clan) {
									$sedner->sendMessage("You dont have a clan");
								}elseif($owner == $sender){
									$sender->sendMessage("You are the owner of this clan");
								}else{
									$pconfig->set("clan", "");
									$sender->sendMessage("You have quit the clan succcessfully");
								}
							}
							elseif(strtolower($arg[1]) == "join") {
								if(!empty ($arg[2])) {
								$clan= $pconfig->get("clan");
								$config = $this->getClanConfig($arg[2]);
								$exist = $config->get("exist");
									if(!empty ($clan)) {
										$sender->sendMessage("You have a clan already");
									}elseif($exist = "false"){
										$sender->sendMessage("The clan doesnt exist!");
									}else{
										$pconfig->set("clan" $arg[2]);
										$sender->sendMessage("You have join the clan $arg[2]");
									}
								}else{
									$sender->sendMessage("Please enter the clan name");
								}
							}else{
								$sender->sendMessage("==============[AVERSION CLAN COMMANDS]==============");
								$sender->sendMessage("/SAO clan join <clan> - join a clan");
								$sender->sendMessage("/SAO clan quit - quit a clan");
								$sender->sendMessage("/SAO clan create <clan> - create a clan");
							}
						}
					}
					elseif(strtolower($arg[0]) == "version"){
						$sender->sendMessage("AversionCore");
						$sender->sendMessage("version: 0.2.4 beta");
						$sender->sendMessage("API: 3.0.0-ALPHA10");
						$sender->sendMessage("author: @DakerOmar");
					}
				}
			}
		}
	}
	public function onHeal(PlayerHeldItemEvent $event) {
		$player = $this->getPlayer();
		$item = $player->getInventory()->getItemInHand()->getId();
		$maxh = $player->getMaxHealth
		if($item == 331) {
			$player->setMaxHealth{$maxh};
		}
	}
	public function shop ($player player, $id, $amount) {
		$config = $this->getPlayerConfig($player->getName());
		switch($id) {
			case 1:
			$price = 1000 * $amount;
			$item = Item::get{DIAMOND_SWORD, 0, $amount};
			break;
			case 2;
			$price = 2000 * $amount;
			$item = Item::get(IRON_SWORD, 0, $amount);
			break;
			case 3;
			$price = 100 * $amount;
			$item = Item::get(GOLDEN_APPLE, 0, $amount);
			break;
			case 4;
			$price = 15000 * $amount;
			$item = Item::get(REDSTONE, 0, $amount);
			break;
			default;
			$player->sendMessage("========[AVERSION TRADING SYSTEM]=======");
			$player->sendMessage("[ID]-[Item]=[Cost]");
			$player->sendMessage("[1]-[DIAMOND_SWORD]-[1000]");
			$player->sendMessage("[2]={IRON_SWORD}-[200]");
			$player->sendMessage("[3]-[GOLDEN_APPLE]-[100]");
			$player->sendMessage("[4]-[Refill_pouder]-[15000]");
			$player->sendMessage("Enter /SAO economy shop <ID> <Amount> to buy");
			return;
		}
		$money = $config->get("money");
		if($money < $price) {
			$player->sendMessage("You dont have enough money!!!");
			return;
		}
		$config->set($money, $money - $price);
		$config->save();
		$player->sendMessage("You have bought that item");
		$player->getInventory()->additem($item);
	}
	public function getnextlevelexp($level) {
        if ($level > 0 and $level < 100) {
		    return $level * 100;
		} else {
			return 0;
		}
	} 
	public function onGetPlayerConfig($name) {
		$config = new Config($this->getDataFolder() . "\\players\\" . strtolower($name) . ".yml", Config::YAML, array(
		    "level" => 1,
			"exp" => 0,
			"money" => 1000,
			"dmoney" => 100,
			"daily-money" => "",
			"wlover" => "",
			"lover" => "",
			"teamm" => "",
			"wteam" => "",
			"clan" => "",
			"logged" => false
			"password" => "",
			"registered" => false
		));
		return $config;
	} 
	public function onGetGameConfig($name) {
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
		    "lobby-level" => "zc"
		));
		return $config;
	} 
	public function onGetClanConfig($name) {
		$config = new Config($this->getDataFolder() . "\\clans\\" . strtolower($name) . ".yml", Config::YAML, array(
		    "name" => $name,
			"money" => 0,
			"owner" => "",
			"exist" => "false",
			"maxp" => 50,
			"nowp" => 1
		));
		return $config;
	}
	public function AddExp($name, $exp) {
		$config = $this->getPlayerConfig($name);
		$xp = $config->get("exp");
		if ($config->get("level") <= 0 or $config->get("level") >= 100) {
			return;
		}
		if($xp + $exp >= $this->getnextlevelexp($config->get("level"))) {
			$xp = $xp + $exp - $this->getnextlevelexp($config->get("level"));
			$config->set("exp", $xp);
			$config->save();
			$this->levelup($name);
		} else {
			$config->set("exp", $xp + $exp);
			$config->save();
		}
	}
//API
	public function getHandHoldItem($killer) {
		$item = $killer->getItemInHand();
		$name = $item->getName();
		return $name
	}
	public function levelup($name) {
		$config = $this->getPlayerConfig($name);
		$level = $config->get("level");
		$level = $level + 1;
		$config->set("level", $level);
		$config->set("money", $config->get("money") + 1000);
		$config->save();
		$player = $this->getServer()->getPlayerExact($name);
		if($player != null) {
			if($player->getMaxHealth() < 160) {
				$player->setMaxHealth($player->getMaxHealth() + 2);
				$player->setHealth($player->getMaxHealth());
			}
		}else{
			$player = $this->getServer()->getOfflinePlayer($name);
			if($player->getMaxHealth() < 160) {
			$player->setMaxHealth($player->getMaxHealth() + 2);	
			$player->setHealth($player->getMaxHealth());
			}
		}
	}
	public function PVP(EntityDamageByEntityEvent $event) {
		$damager = $event->getDamager();
		$bdamage = $event->getEntity();
		$item = $damager->getInventory()->getItemInHand();
		$id = $item->getId();
		$damage = $item->getDamage();
		$config = $this->getPlayerConfig($damager->getName());
		$teamm = $config->get("teamm");
		$lover = $config->get("lover");
		$hdamage = $event->getDamage();
		if($damager instanceof Player && $bdamager instanceof Entity) {
			if ($bdamager->getHealth() - $hdamage <= 0) {
				$damager->AddExp($damager->getName(), 10)
			}
		}
		elseif($damager instanceof Player && $bdamager instanceof Player) {
			if(in_array($damager->getLevel()->getName(), $pvpcfg->get("UnPVPWorld")) && !$damager->isOp()) {
				$damager->sendMessage($pvpcfg->get("UnPVPMessage"));
			}
			elseif($teamm == $bdamager->getName()){
				$event->setCancelled(true);
				$damager->sendMessage("You cant hurt your teammate!");
			}
			elseif($lover == $bdamager->getName()){
				$event->setCancelled(true);
				$damager->sendMessage("You cant hurt your partner!");
			}
			elseif ($bkiller->getHealth() - $event->getDamage() <= 0) {
				$this->plugin->addExp($killer->getName(), 20);
				$damager->sendMessage("You have kill" . $bdamager->getName() . ",Got 20 Exp");
			}else{
			        if($id == 351) {
					if($dmaage = 0) {
				        	$event->setDamage(10);
					        $damager->sendMessage("You use skills");
				        }
				        elseif($damage == 1) {
		       			        $event->setDamage(5);
					        $damager->sendMessage("You use skills");
				        }
				        elseif($damage == 2) {
					        $event->setDamage(5);
					        $damager->sendMessage("You use skills");
				        }
				        elseif($damage == 3) {
				        	$event->setDamage(5);
				        	$damager->sendMessage("You use skills");
				        }
			        	elseif($damage == 4) {
				        	$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 5) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 6) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 7) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 8) {
			        		$event->setDamage(5);
                            $damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 9) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
				        elseif($damage == 10) {
			        		$event->setDamage(5);
			         		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 11) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
			        	elseif($damage == 12) {
			        		$event->setDamage(5);
			        		$damager->sendMessage("You use skills");
			        	}
				        elseif($damage == 13) {
				        	$event->setDamage(5);
				        	$damager->sendMessage("You use skills");
				        }
				        elseif($damage == 14) {
					        $event->setDamage(5);
					        $damager->sendMessage("You use skills");
				        }
	        			elseif($damage == 15) {
	        				$event->setDamage(5);
	        				$damager->sendMessage("You use skills");
	        			}
				}
			}
		}
	}
}
